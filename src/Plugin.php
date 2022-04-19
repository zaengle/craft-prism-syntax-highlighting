<?php
/**
 * Prism Syntax Highlighting plugin for Craft CMS 3.x
 *
 * Adds a new field type that provides syntax highlighting capabilities using PrismJS.
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith <me@joshsmith.dev>
 */

namespace thejoshsmith\prismsyntaxhighlighting;

use thejoshsmith\prismsyntaxhighlighting\variables\PrismSyntaxHighlightingVariable;
use thejoshsmith\prismsyntaxhighlighting\twigextensions\PrismSyntaxHighlightingTwigExtension;
use thejoshsmith\prismsyntaxhighlighting\models\Settings;
use thejoshsmith\prismsyntaxhighlighting\services\Files;
use thejoshsmith\prismsyntaxhighlighting\services\PrismSyntaxHighlighting;
use thejoshsmith\prismsyntaxhighlighting\fields\PrismSyntaxHighlightingField;

use Craft;
use craft\base\Plugin as CraftPlugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\web\View;
use craft\events\TemplateEvent;

use yii\base\Event;

/**
 * Class PrismSyntaxHighlighting
 *
 * @author    Josh Smith <me@joshsmith.dev>
 * @package   PrismSyntaxHighlighting
 * @since     1.0.0
 *
 */
class Plugin extends CraftPlugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var PrismSyntaxHighlighting
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new PrismSyntaxHighlightingTwigExtension());

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = PrismSyntaxHighlightingField::class;
            }
        );

        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function(TemplateEvent $event) {
                if( !Craft::$app->getRequest()->getIsCpRequest() ) return;

                $assetBundles =& $event->sender->assetBundles;
                $prismJsAsset = 'craft\\web\\assets\\prismjs\\PrismJsAsset';

                // Prevent Craft from loading the CMS version of PrismJS.
                // We make sure to load the languages Craft requires in the Plugin.
                if( array_key_exists($prismJsAsset, $assetBundles) ){
                    $assetBundles[$prismJsAsset]->js = [];
                }
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('prismSyntaxHighlighting', PrismSyntaxHighlightingVariable::class);
            }
        );

        // Register the prism files service
        Craft::$app->setComponents([
            'prismFilesService' => Files::class,
            'prismService' => PrismSyntaxHighlighting::class,
        ]);

        Craft::info(
            Craft::t(
                'craft-prism-syntax-highlighting',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'craft-prism-syntax-highlighting/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }


    /**
     * Copy example config to project's config folder
     */
    protected function afterInstall(): void
    {
        $configSource = __DIR__ . DIRECTORY_SEPARATOR . 'config/prismsyntaxhighlighting.php';
        $configTarget = Craft::$app->getConfig()->configDir . DIRECTORY_SEPARATOR . 'prismsyntaxhighlighting.php' ;

        if (!file_exists($configTarget)) {
            copy($configSource, $configTarget);
        }
    }
}
