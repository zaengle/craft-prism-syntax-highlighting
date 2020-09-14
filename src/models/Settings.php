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
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array
     */
    public $editorThemes = ['prism'];

    /**
     * @var array
     */
    public $editorLanguages = ['css','javascript','markup','json'];

    /**
     * @var array
     */
    public $editorPlugins = ['line-numbers'];

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
    public $customThemesDir = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns an array of themes for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @param array $definitions An array of existing theme definitions
     * @return array
     */
    public function getThemes($definitions = [])
    {
        $userThemes = [];
        $themes = Plugin::$plugin->prismService->getDefinitions('themes', $definitions);

        if( !empty($this->customThemesDir) ){
            $prismConfig = Plugin::$plugin->prismService->getConfig('themes');
            $userThemes = array_diff_key($prismConfig, $themes);
            $userThemes = Plugin::$plugin->prismService->parseCustomThemeDefinitions($userThemes);
        }

        return array_merge($themes, $userThemes);
    }

    /**
     * Returns an array of languages for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @param array $definitions An array of existing language definitions
     * @return array
     */
    public function getLanguages($definitions = [])
    {
        return Plugin::$plugin->prismService->getDefinitions('languages', $definitions);
    }

    /**
     * Returns an array of plugins for the twig templates
     * @author Josh Smith <josh@batch.nz>
     * @param  array  $definitions An array of existing plugin definitions
     * @return array
     */
    public function getPlugins($definitions = [])
    {
        return Plugin::$plugin->prismService->getDefinitions('plugins', $definitions);
    }

    /**
     * Returns an array of available editor themes for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    public function getEditorThemes()
    {
        return $this->getThemes($this->editorThemes);
    }

    /**
     * Returns an array of available editor languages for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    public function getEditorLanguages()
    {
        return $this->getLanguages($this->editorLanguages);
    }

    /**
     * Returns an array of available editor plugins for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    public function getEditorPlugins()
    {
        return $this->getPlugins($this->editorPlugins);
    }

    public function hasEditorPlugin(string $plugin)
    {
        return array_key_exists($plugin, $this->getEditorPlugins());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['editorThemes', 'each', 'rule' => ['string']],
            ['editorLanguages', 'each', 'rule' => ['string']],
            ['editorHeight', 'string'],
            ['editorHeight', 'default', 'value' => '4'],
            ['editorTabWidth', 'string'],
            ['editorTabWidth', 'default', 'value' => '4'],
            ['customThemesDir', 'string']
        ];
    }
}
