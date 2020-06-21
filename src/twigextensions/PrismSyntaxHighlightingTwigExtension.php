<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting\twigextensions;

use Craft;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use thejoshsmith\prismsyntaxhighlighting\Plugin;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     2.0.0
 */
class PrismSyntaxHighlightingTwigExtension extends AbstractExtension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Prism Syntax Highlighting';
    }

    /**
     * Returns an array of Twig filters
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('highlight', [$this, 'render']),
        ];
    }

    /**
     * Our function called via Twig; it can do anything you want
     *
     * @param mixed $options
     * @return void
     */
    public function render($options)
    {
        if( !is_array($options) ){
            $options = func_get_args();
        }

        echo call_user_func_array([Plugin::$plugin->prismEditorService, 'render'], $options);
    }
}
