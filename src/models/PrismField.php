<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting\models;

use thejoshsmith\prismsyntaxhighlighting\Plugin;
use thejoshsmith\prismsyntaxhighlighting\assetbundles\prismsyntaxhighlighting\PrismSyntaxHighlightingAsset;

use Craft;
use craft\base\Model;
use craft\events\TemplateEvent;
use craft\web\View;
use yii\base\Event;

/**
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     2.0.0
 */
class PrismField extends Model {

    /**
     * A static array of theme asset files to be registered
     * @var array
     */
    public static $editorThemeFiles = [];

    /**
     * A static array of language asset files to be registered
     * @var array
     */
    public static $editorLanguageFiles = [];

     /**
     * Populated from the field's value
     * @var string
     */
    public $code = '';

    /**
     * Populated from the field's value
     * @var string
     */
    public $editorTheme = '';

    /**
     * Populated from the field's value
     * @var string
     */
    public $editorLanguage = '';

    /**
     * Returns whether there are PrismJS asset files to be registered
     * @author Josh Smith <josh@batch.nz>
     * @return boolean
     */
    public static function hasAssetFiles(): bool
    {
        return !empty(self::$editorThemeFiles) || !empty(self::$editorLanguageFiles);
    }

    /**
     * Registers field PrismJS asset files
     * This loads all assets required to use PrismJS on the front end
     * @author Josh Smith <josh@batch.nz>
     * @return void
     */
    public static function registerAssetFiles()
    {
        if( !self::hasAssetFiles() ) return;

        $prismFilesService = Plugin::$plugin->prismFilesService;

        // $prismFilesService->registerPrismJsAssetBundle();
        Craft::$app->getView()->registerAssetBundle(PrismSyntaxHighlightingAsset::class);
        $prismFilesService->registerEditorThemesAssetBundle(PrismField::$editorThemeFiles);
        $prismFilesService->registerEditorLanguageAssetBundle(PrismField::$editorLanguageFiles);
    }

    /**
     * Returns a theme class
     * @author Josh Smith <josh@batch.nz>
     * @param  string $theme
     * @return string
     */
    public function getThemeClass($theme = ''): string
    {
        return $theme;
    }

    /**
     * Returns a language class
     * @author Josh Smith <josh@batch.nz>
     * @param  string $language
     * @return string
     */
    public function getLanguageClass($language = ''): string
    {
        return 'language-' . $language;
    }

    /**
     * Renders the syntax highlighting field
     * @author Josh Smith <josh@batch.nz>
     * @param  string $tag
     * @return string
     */
    public function render()
    {
        $prismFilesService = Plugin::$plugin->prismFilesService;

        $editorThemeFile = $prismFilesService->getEditorThemeFile($this->editorTheme);
        $editorLanguageFiles = $prismFilesService->getEditorLanguageFile($this->editorLanguage);

        // Keep track of the theme and language files we need to load
        // These are stored statically on this class and then registered before the view is loaded using View::EVENT_END_BODY
        self::$editorThemeFiles[] = $editorThemeFile;
        self::$editorLanguageFiles = array_merge($editorLanguageFiles, self::$editorLanguageFiles);

        $code = <<<EOD
        <div class="{$this->getThemeClass($this->editorTheme)}"><pre class="{$this->getLanguageClass($this->editorLanguage)}"><code class="{$this->getLanguageClass($this->editorLanguage)}">$this->code</code></pre></div>
EOD;

        return new \Twig_Markup($code, 'UTF-8');
    }
}
