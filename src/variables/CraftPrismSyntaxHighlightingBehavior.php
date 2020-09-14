<?php
/**
 * Craft Commerce Multi Vendor plugin for Craft CMS 3.x
 *
 * Support multi vendors on your Craft Commerce store.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith
 */

namespace thejoshsmith\prismsyntaxhighlighting\variables;

use thejoshsmith\prismsyntaxhighlighting\Plugin;
use thejoshsmith\prismsyntaxhighlighting\elements\Order as SubOrder;
use thejoshsmith\prismsyntaxhighlighting\db\OrderQuery as SubOrderQuery;
use thejoshsmith\prismsyntaxhighlighting\elements\Vendor;
use thejoshsmith\prismsyntaxhighlighting\elements\db\VendorQuery;
use thejoshsmith\prismsyntaxhighlighting\records\VendorType;
use yii\base\Behavior;

use Craft;

/**
 * Craft Commerce Multi Vendor Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.craftCommerceMultiVendor }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Josh Smith
 * @package   CraftCommerceMultiVendor
 * @since     1.0.0
 */
class CraftPrismSyntaxHighlightingBehavior extends Behavior
{
    public function prism()
    {
        return Plugin::$plugin->prismService;
    }
}
