<?php

/**
 * Prism Syntax Highlighting - Editor Service
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith
 */

namespace thejoshsmith\prismsyntaxhighlighting\services;

use thejoshsmith\prismsyntaxhighlighting\Plugin;
use thejoshsmith\prismsyntaxhighlighting\plugins\AbstractPlugin;
use thejoshsmith\prismsyntaxhighlighting\assetbundles\prismsyntaxhighlighting\PrismSyntaxHighlightingAsset;

use Craft;
use craft\base\Component;
use craft\web\View;
use craft\base\Field;
use yii\web\AssetBundle;

/**
 * Prism Syntax highlighting Editor Service
 * Responsible for editor business logic
 * @author    Josh Smith
 * @package   Prism Syntax Highlighting
 * @since     2.0.0
 */
class Editor extends Component
{
    const PLUGIN_CLASSES_HOOK = 'pluginClassesHook';

    /**
     * A static array of theme asset files to be registered
     * @var array
     */
    public static $editorThemes = [];

    /**
     * A static array of language asset files to be registered
     * @var array
     */
    public static $editorLanguages = [];

    /**
     * A static array of plugin asset files to be registered
     * @var array
     */
    public static $editorPlugins = [];

    /**
     * @var array List of registered plugin classes
     */
    private static $_registeredPlugins = [];

    // *
    //  * @var array List of registered hooks

    // private static $_registeredHooks = [];

    /**
     * Returns a theme class from the passed theme name
     * @author Josh Smith <josh@batch.nz>
     * @param  string $theme Name of the theme
     * @return string
     */
    public function getThemeClass(string $theme): string
    {
        return $theme;
    }

    /**
     * Returns a language class from the passed language name
     * @author Josh Smith <josh@batch.nz>
     * @param  string $language Name of the language
     * @return string
     */
    public function getLanguageClass(string $language): string
    {
        return 'language-' . $language;
    }

    /**
     * Returns whether there are PrismJS asset files to be registered
     * @author Josh Smith <josh@batch.nz>
     * @return boolean
     */
    public function hasTemplateAssetFiles(): bool
    {
        return !empty(self::$editorThemes) || !empty(self::$editorLanguages);
    }

    /**
     * Registers field PrismJS asset files
     * This loads all assets required to use PrismJS on the front end
     * @author Josh Smith <josh@batch.nz>
     * @return void
     */
    public function registerTemplateAssetFiles(): ?AssetBundle
    {
        if( !$this->hasTemplateAssetFiles() ) return null;
        return $this->registerAssetFiles(self::$editorLanguages, self::$editorThemes);
    }

    /**
     * Registers asset files required by a prism editor
     * Defaults the languages and themes to the plugin settings
     * Takes an array of language and theme definitions and loads/publishes the required asset files into the view
     *
     * @author Josh Smith <josh@batch.nz>
     * @param  array  $editorLanguages An array of prism language definitions
     * @param  array  $editorThemes    An array of prism theme definitions
     * @param  array  $editorPlugins   An array of prism plugin definitions
     * @return AssetBundle
     */
    public function registerAssetFiles(array $editorLanguages = [], array $editorThemes = [], array $editorPlugins = []): AssetBundle
    {
        $settings = Plugin::$plugin->getSettings();

        if( empty($editorLanguages) ){
            $editorLanguages = $settings->editorLanguages;
        }

        if( empty($editorThemes) ){
            $editorThemes = $settings->editorThemes;
        }

        if( empty($editorPlugins) ){
            $editorPlugins = array_filter($settings->editorPlugins);
        }

        $view = Craft::$app->getView();
        $prismFilesService = Plugin::$plugin->prismFilesService;

        // Fetch the theme and language files we need to load from the definitions
        $editorThemeFiles = $prismFilesService->getEditorThemeAssetBundleFiles($editorThemes);
        $editorPluginFiles = $prismFilesService->getEditorPluginAssetBundleFiles($editorPlugins);
        $editorLanguageFiles = $prismFilesService->getEditorLanguageAssetBundleFiles($editorLanguages);

        $files = array_merge($editorThemeFiles, $editorLanguageFiles, $editorPluginFiles);

        return $this->registerAssetBundle($files);
    }

     /**
     * Renders the syntax highlighting field
     * @author Josh Smith <josh@batch.nz>
     * @param  mixed  $code             Twig_Markup object or string
     * @param  string $editorLanguage   The editor language to highlight output in
     * @param  string $editorTheme      The editor theme
     * @return string
     */
    public function render($code = '', string $editorLanguage = 'markup', string $editorTheme = 'prism')
    {
        // Keep track of the themes and languages we need to load
        // These are stored statically on this class and then registered before the view is loaded using View::EVENT_END_PAGE
        self::$editorThemes[] = $editorTheme;
        self::$editorLanguages[] = $editorLanguage;

        // $this->registerPluginClassHooks($namespace);

        $oldMode = Craft::$app->getView()->getTemplateMode();
        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);

        $variables = [
            'themeClass' => $this->getThemeClass($editorTheme),
            'languageClass' => $this->getLanguageClass($editorLanguage),
            'code' => $code,
            'settings' => Plugin::$plugin->getSettings()
        ];

        $html = Craft::$app->getView()->renderTemplate('craft-prism-syntax-highlighting/_render/codeBlock', $variables);
        Craft::$app->getView()->setTemplateMode($oldMode);

        return $html;
    }

    // /**
    //  * Registers prism editor plugins
    //  * @author Josh Smith <josh@batch.nz>
    //  * @param  array $plugins
    //  * @return void
    //  */
    // public function registerPlugins(array $plugins)
    // {
    //     // Attempt to autoload selected plugins
    //     foreach ($plugins as $handle) {
    //         $className = str_replace('-', '', ucwords($handle, '-'));
    //         $class = "\\thejoshsmith\\prismsyntaxhighlighting\\plugins\\$className";
    //         $classExists = class_exists($class);

    //         if( !$classExists ) continue;

    //         $definition = (array) Plugin::$plugin->prismService->getPluginDefinition($handle);

    //         $plugin = new $class($handle, $definition);

    //         static::$_registeredPlugins[] = $plugin;
    //     }
    // }

    // *
    //  * Registers field input html hooks
    //  * @author Josh Smith <josh@batch.nz>
    //  * @param  Field  $field Highlighting field
    //  * @return void

    // public function registerPluginInputHtmlHooks(Field $field)
    // {

    // }

    // /**
    //  * Registers field class hooks with the plugins
    //  * @author Josh Smith <josh@batch.nz>
    //  * @return void
    //  */
    // public function registerPluginClassHooks(string $namespace = '')
    // {
    //     // if( !empty(self::$_registeredHooks[self::PLUGIN_CLASSES_HOOK]) ) return;

    //     foreach (self::$_registeredPlugins as $plugin) {
    //         Craft::$app->view->hook($namespace.'-editor-pre-classes-hook', function(array &$context) use($plugin) {
    //             return $plugin->getPreClassesHook($context);
    //         });

    //         Craft::$app->view->hook($namespace.'-editor-code-classes-hook', function(array &$context) use($plugin) {
    //             return $plugin->getCodeClassesHook($context);
    //         });
    //     }

    //     // // Mark this hook as registered
    //     // self::$_registeredHooks[self::PLUGIN_CLASSES_HOOK] = true;
    // }

    /**
     * Registers the asset bundle with optional extra files
     * @author Josh Smith <josh@batch.nz>
     * @param  array  $extraFiles An array of additional files to load
     * @return AssetBundle
     */
    protected function registerAssetBundle(array $extraFiles = []): AssetBundle
    {
        $view = Craft::$app->getView();
        $prismFilesService = Plugin::$plugin->prismFilesService;

        // Extract the js and css files we need
        $editorJsFiles = $prismFilesService->filterJsFiles($extraFiles);
        $editorCssFiles = $prismFilesService->filterCssFiles($extraFiles);

        // Register the prism asset bundle
        $assetBundle = PrismSyntaxHighlightingAsset::register($view);
        $assetBundle->js = array_merge($assetBundle->js, $editorJsFiles);
        $assetBundle->css = array_merge($assetBundle->css, $editorCssFiles);

        // Register asset bundle files with the view
        $assetBundle->registerAssetFiles($view);

        return $assetBundle;
    }
}
