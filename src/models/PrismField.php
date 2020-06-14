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

use Craft;
use craft\base\Model;

/**
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     2.0.0
 */
class PrismField extends Model {

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

        $frontEndAssetBundle = $prismFilesService->registerPrismJsAssetBundle();
        $frontEndAssetBundle = $prismFilesService->registerEditorThemesAssetBundle([$editorThemeFile]);
        $frontEndAssetBundle = $prismFilesService->registerEditorLanguageAssetBundle($editorLanguageFiles);

        Craft::$app->getView()->endBody();

        $code = <<<EOD
        <pre class="{$this->getThemeClass($this->editorTheme)} {$this->getLanguageClass($this->editorLanguage)}"><code class="{$this->getLanguageClass($this->editorLanguage)}">$this->code</code></pre>
EOD;

        return new \Twig_Markup($code, 'UTF-8');
    }
}
