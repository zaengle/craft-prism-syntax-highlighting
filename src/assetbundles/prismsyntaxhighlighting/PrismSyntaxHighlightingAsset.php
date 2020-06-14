<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting\assetbundles\prismsyntaxhighlighting;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     1.0.0
 */
class PrismSyntaxHighlightingAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@thejoshsmith/prismsyntaxhighlighting/assetbundles/prismsyntaxhighlighting/dist";

        // Core Prism Scripts
        $this->js = [
            'js/prism/components/prism-core.min.js',
        ];

        // Load these assets on site requests
        if( Craft::$app->getRequest()->getIsSiteRequest() ) {
            $this->css = [
                'css/PrismJs.css'
            ];
        }

        // Load these assets on CP requests
        if( Craft::$app->getRequest()->getIsCpRequest() ){
            $this->depends = [
                CpAsset::class
            ];

            $this->js = array_merge([
                // Keypress management
                'js/bililiteRange/bililiteRange.js',
                'js/bililiteRange/bililiteRange.fancytext.js',
                'js/bililiteRange/bililiteRange.undo.js',
                'js/bililiteRange/bililiteRange.util.js',
                'js/bililiteRange/jquery.sendkeys.js',

                // Main Script
                'js/PrismSyntaxHighlighting.js'
            ], $this->js);

            $this->css = [
                'css/PrismSyntaxHighlighting.css',
            ];
        }

        // Only publish required files
        $this->publishOptions = [
            'only' => [
                'js/prism/components/prism-core.min.js',
                'js/bililiteRange/**',
                'js/PrismSyntaxHighlighting.js',
                'css/PrismSyntaxHighlighting.css',
                'css/PrismJs.css',
            ]
        ];

        parent::init();
    }
}
