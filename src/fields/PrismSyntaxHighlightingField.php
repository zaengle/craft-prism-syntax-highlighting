<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting\fields;

use thejoshsmith\prismsyntaxhighlighting\Plugin;
use thejoshsmith\prismsyntaxhighlighting\models\PrismField;
use thejoshsmith\prismsyntaxhighlighting\assetbundles\prismsyntaxhighlighting\PrismSyntaxHighlightingAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use craft\helpers\Json;
use yii\db\Schema;
use yii\web\AssetBundle;

/**
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     1.0.0
 */
class PrismSyntaxHighlightingField extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $defaultEditorTheme = '';

    // /**
    //  * @var string
    //  */
    // public $editorThemeFile = '';

    /**
     * @var string
     */
    public $defaultEditorLanguage = '';

    // /**
    //  * @var string
    //  */
    // public $editorLanguageFile = [];

    /**
     * @var string
     */
    public $editorHeight = '4';

    /**
     * @var string
     */
    public $editorTabWidth = '4';

    /**
     * @var string
     */
    public $editorLineNumbers = false;

    /**
     * @var string
     */
    protected $prismFieldModel;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('craft-prism-syntax-highlighting', 'Prism Syntax Highlighting');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['editorHeight', 'string'],
            ['editorHeight', 'default', 'value' => '4'],
            ['editorTabWidth', 'string'],
            ['editorTabWidth', 'default', 'value' => '4'],
            ['editorLineNumbers', 'boolean'],
            ['editorLineNumbers', 'default', 'value' => false],
        ]);
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if( is_string($value) ){
            $value = Json::decode($value, true);
        }

        // Assign a reference to the prism field model
        $this->prismFieldModel = new PrismField($value);

        return $this->prismFieldModel;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $settings = Plugin::$plugin->getSettings();

        // Allow the lightswitch to use defaults if it's a new field.
        // It's set to a boolean by default, so we need to nullify it.
        if( $this->id === null ){
            $this->editorLineNumbers = null;
        }

        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'craft-prism-syntax-highlighting/_components/fields/PrismSyntaxHighlightingField_settings',
            [
                'field' => $this,
                'defaults' => $settings
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $settings = Plugin::$plugin->getSettings();
        $prismFilesService = Plugin::$plugin->prismFilesService;

        // Load asset bundles
        $prismSyntaxHighlightingAsset = Craft::$app->getView()->registerAssetBundle(PrismSyntaxHighlightingAsset::class);
        $themeAssetBundle = $prismFilesService->registerEditorThemesAssetBundle($settings->editorThemeFiles);
        $languageAssetBundle = $prismFilesService->registerEditorLanguageAssetBundle($settings->editorLanguageFiles);

        // Register the line numbers plugin js and css
        if( $this->editorLineNumbers === '1' ){
            $prismSyntaxHighlightingAsset->js[] = 'js/prism/plugins/line-numbers/prism-line-numbers.min.js';
            $prismSyntaxHighlightingAsset->css[] = 'js/prism/plugins/line-numbers/prism-line-numbers.css';
        }

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];

        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').PrismSyntaxHighlightingField(" . $jsonVars . ");");

        // Set editor theme and language
        $editorTheme = (empty($this->prismFieldModel->editorTheme) ? $this->defaultEditorTheme : $this->prismFieldModel->editorTheme);
        $editorLanguage = (empty($this->prismFieldModel->editorLanguage) ? $this->defaultEditorLanguage : $this->prismFieldModel->editorLanguage);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'craft-prism-syntax-highlighting/_components/fields/PrismSyntaxHighlightingField_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'code' => $this->prismFieldModel->code ?? '',
                'editorLineNumbers' => $this->editorLineNumbers,
                'editorLanguageClass' => $this->prismFieldModel->getLanguageClass($editorLanguage),
                'editorThemeClass' => $this->prismFieldModel->getThemeClass($editorTheme),
                'editorTheme' => $editorTheme,
                'editorLanguage' => $editorLanguage,
                'editorHeight' => $this->editorHeight,
                'editorTabWidth' => $this->editorTabWidth,
                'settings' => $settings
            ]
        );
    }
}
