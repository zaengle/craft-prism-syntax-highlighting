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
class PrismJsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@thejoshsmith/prismsyntaxhighlighting/assetbundles/prismsyntaxhighlighting/dist";

        $this->js = [
            'js/prism/components/prism-core.min.js',
        ];

        parent::init();
    }
}
