<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting;

use thejoshsmith\prismsyntaxhighlighting\variables\PrismSyntaxHighlightingVariable;
use thejoshsmith\prismsyntaxhighlighting\twigextensions\PrismSyntaxHighlightingTwigExtension;
use thejoshsmith\prismsyntaxhighlighting\models\Settings;
use thejoshsmith\prismsyntaxhighlighting\services\Files;
use thejoshsmith\prismsyntaxhighlighting\services\PrismSyntaxHighlighting;
use thejoshsmith\prismsyntaxhighlighting\fields\PrismSyntaxHighlightingField;

use Craft;
use craft\base\Plugin as CraftPlugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class PrismSyntaxHighlighting
 *
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     1.0.0
 *
 */
class Plugin extends CraftPlugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var PrismSyntaxHighlighting
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new PrismSyntaxHighlightingTwigExtension());

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = PrismSyntaxHighlightingField::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('prismSyntaxHighlighting', PrismSyntaxHighlightingVariable::class);
            }
        );

        // Register the prism files service
        Craft::$app->setComponents([
            'prismFilesService' => Files::class,
            'prismService' => PrismSyntaxHighlighting::class,
        ]);

        Craft::info(
            Craft::t(
                'craft-prism-syntax-highlighting',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * Resolves filepaths for the plugin's themes and languages
     * It's more efficient to do this here, rather than dynamically processing on load.
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  bool   $isNew
     * @return void
     */
    public function beforeSaveSettings(): bool
    {
        $prismService = self::$plugin->prismService;
        $prismFilesService = self::$plugin->prismFilesService;
        $prismSettings = self::$plugin->getSettings();

        $editorThemeFiles = [];
        $editorLanguageFiles = [];

        // Store the fully qualified theme paths
        foreach ($prismSettings->editorThemes as $file) {
            $editorThemeFiles[] = $prismFilesService->getEditorFile(
                $file.'.css', // Ok to hardcode here, it's the only place it's used.
                $prismFilesService::PRISM_THEMES_DIR,
                $prismSettings->customThemesDir
            );
        }

        // Store the fully qualified syntax file paths
        foreach ($prismSettings->editorLanguages as $language) {
            // Load the language requirements
            $editorLanguageFileRequirements = $prismService->getLanguageDefinitionRequirements($language);
            // $editorLanguageFileDefinitions = array_merge($editorLanguageFileRequirements, [$language]);

            // Loop all language requirements and resolve the filepaths
            foreach ($editorLanguageFileRequirements as $file) {
                $filename = 'prism-'.$file.'.min.js'; // Ok to hardcode here, it's the only place it's used.
                $editorLanguageFiles[] = $prismFilesService->getEditorFile(
                    $filename,
                    $prismFilesService::PRISM_LANGUAGES_DIR
                );
            }
        }

        // Store the updated settings on the plugin model
        self::setSettings([
            'editorThemeFiles' => $editorThemeFiles,
            'editorLanguageFiles' => $editorLanguageFiles
        ]);

        return parent::beforeSaveSettings();
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-prism-syntax-highlighting/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
