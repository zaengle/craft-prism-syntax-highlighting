<?php
/**
 * Prism Syntax Highlighting - Files Service
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith
 */

namespace thejoshsmith\prismsyntaxhighlighting\services;

use Craft;
use craft\base\Component;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;
use thejoshsmith\prismsyntaxhighlighting\Plugin;
use thejoshsmith\prismsyntaxhighlighting\services\PrismSyntaxHighlighting;
use thejoshsmith\prismsyntaxhighlighting\assetbundles\prismsyntaxhighlighting\PrismSyntaxHighlightingAsset;

/**
 * Prism Syntax highlighting Files Service
 * @author    Josh Smith
 * @package   Prism Syntax Highlighting
 * @since     1.0.0
 */
class Files extends Component
{
    /**
     * Constant filepaths
     * @var string
     */
    const PRISM_DIST_DIR = '@thejoshsmith/prismsyntaxhighlighting/assetbundles/prismsyntaxhighlighting/dist';
    const PRISM_THEMES_DIR = 'css/prism/themes/';
    const PRISM_LANGUAGES_DIR = 'js/prism/components/';
    const PRISM_PLUGINS_DIR = 'js/prism/plugins/';

    public function getEditorThemeFiles(array $files = [], $dir = self::PRISM_THEMES_DIR, $customDir = ''): array
    {
        $editorThemeFiles = [];

        // Store the fully qualified theme paths
        foreach ($files as $file) {
            $editorThemeFiles[] = $this->getEditorThemeFile($file);
        }

        return $editorThemeFiles;
    }

    public function getEditorThemeFile(string $file, $dir = self::PRISM_THEMES_DIR, $customDir = ''): string
    {
        return $dir.$file.'.css';
    }

    public function getEditorLanguageFiles(array $files, $dir = self::PRISM_LANGUAGES_DIR): array
    {
        $editorLanguageFiles = [];

        // Store the fully qualified theme paths
        foreach ($files as $file) {
            $editorLanguageFiles = array_merge($editorLanguageFiles, $this->getEditorLanguageFile($file));
        }

        return $editorLanguageFiles;
    }

    public function getEditorLanguageFile(string $file, $dir = self::PRISM_LANGUAGES_DIR): array
    {
        $prismService = Plugin::$plugin->prismService;

        $editorLanguageFiles = [];
        $editorLanguageFileRequirements = $prismService->getLanguageDefinitionRequirements($file);

        // Loop all language requirements and resolve the filepaths
        foreach ($editorLanguageFileRequirements as $requirement) {
            $filename = 'prism-'.$requirement.'.min.js'; // Ok to hardcode here, it's the only place it's used.
            $editorLanguageFiles[] = $dir.$filename;
        }

        return $editorLanguageFiles;
    }

    public function getEditorPluginFiles(array $files, $dir = self::PRISM_PLUGINS_DIR): array
    {
        $editorPluginFiles = [];

        foreach ($files as $file) {
            $editorPluginFiles = array_merge($editorPluginFiles, $this->getEditorPluginFile($file));
        }

        return $editorPluginFiles;
    }

    public function getEditorPluginFile(string $file, $dir = self::PRISM_PLUGINS_DIR): array
    {
        $prismService = Plugin::$plugin->prismService;

        $editorFiles = [];
        $editorPrismFileRequirements = $prismService->getPluginDefinitionRequirements($file);

        // Loop all language requirements and resolve the filepaths
        foreach ($editorPrismFileRequirements as $requirement) {
            $definition = $prismService->getPluginDefinition($requirement);
            $filepath = $dir.$requirement.'/'.'prism-'.$requirement;

            if( empty($definition->noCSS) ){
                $editorFiles[] = $filepath.'.css';
            }
            $editorFiles[] = $filepath.'.min.js';
        }

        return $editorFiles;
    }

    /**
     * Returns editor theme asset files for an asset bundle to load
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $filename
     * @return AssetBundle
     */
    public function getEditorThemeAssetBundleFiles(array $themes)
    {
        // Fetch the theme files
        return $this->getEditorThemeFiles($themes);
    }

    /**
     * Returns editor language asset files for an asset bundle to load
     * @author Josh Smith <me@joshsmith.dev>
     * @param  array $files
     * @return AssetBundle
     */
    public function getEditorLanguageAssetBundleFiles(array $languages)
    {
        // Fetch the language files
        $files = $this->getEditorLanguageFiles($languages);

        // Load Craft required CP languages
        if( Craft::$app->getRequest()->getIsCpRequest() ) {
            $craftCpLanguages = $this->getEditorLanguageFiles(PrismSyntaxHighlighting::CRAFTCMS_CP_LANGUAGES);
            $files = array_unique(array_merge($files, $craftCpLanguages));
        }

        return $files;
    }

    /**
     * Returns editor plugin asset files for an asset bundle to load
     * @author Josh Smith <me@joshsmith.dev>
     * @param  array $files
     * @return AssetBundle
     */
    public function getEditorPluginAssetBundleFiles(array $plugins)
    {
        return $this->getEditorPluginFiles($plugins);
    }

    /**
     * Returns a filtered set of JS files
     * @author Josh Smith <josh@batch.nz>
     * @param  array  $files An array of files to filter
     * @return array
     */
    public function filterJsFiles(array $files = []): array
    {
        return array_filter($files, function($file){ return substr(strrchr($file,'.'), 1) === 'js'; });
    }

    /**
     * Returns a filtered set of CSS files
     * @author Josh Smith <josh@batch.nz>
     * @param  array  $files An array of files to filter
     * @return array
     */
    public function filterCssFiles(array $files = []): array
    {
        return array_filter($files, function($file){ return substr(strrchr($file,'.'), 1) === 'css'; });
    }

    /**
     * Parses out a prism name into a human readable name
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $name
     * @return string
     */
    protected function parsePrismName(string $name): string
    {
        $name = explode('-', $name);

        if( count($name) > 1 ){
            array_shift($name);
        }

        return ucwords(implode(' ', $name));
    }
}
