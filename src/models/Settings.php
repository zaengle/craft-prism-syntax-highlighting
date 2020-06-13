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
    public $editorThemeFiles = [];

    /**
     * @var array
     */
    public $editorLanguages = ['css','javascript','markup'];

    /**
     * @var array
     */
    public $editorLanguageFiles = [];

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
     * Returns an array of editor themes for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    public function getEditorThemes()
    {
        return $this->getThemes($this->editorThemes);
    }

    /**
     * Returns an array of editor languages for the twig templates
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    public function getEditorLanguages()
    {
        return $this->getLanguages($this->editorLanguages);
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
            ['editorLineNumbers', 'boolean'],
            ['editorLineNumbers', 'default', 'value' => false],
            ['customThemesDir', 'string']
        ];
    }
}
