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
        return self::PRISM_THEMES_DIR.$file.'.css';
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
            $editorLanguageFiles[] = self::PRISM_LANGUAGES_DIR.$filename;
        }

        return $editorLanguageFiles;
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
