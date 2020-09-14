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

use thejoshsmith\prismsyntaxhighlighting\models\PrismField;
use thejoshsmith\prismsyntaxhighlighting\models\Settings;
use thejoshsmith\prismsyntaxhighlighting\services\Editor;
use thejoshsmith\prismsyntaxhighlighting\services\Files;
use thejoshsmith\prismsyntaxhighlighting\services\PrismSyntaxHighlighting;
use thejoshsmith\prismsyntaxhighlighting\fields\PrismSyntaxHighlightingField;
use thejoshsmith\prismsyntaxhighlighting\twigextensions\PrismSyntaxHighlightingTwigExtension;
use thejoshsmith\prismsyntaxhighlighting\variables\CraftPrismSyntaxHighlightingBehavior;

use Craft;
use craft\base\Plugin as CraftPlugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\TemplateEvent;
use craft\services\Fields;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;

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
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = PrismSyntaxHighlightingField::class;
            }
        );

        /**
         * Register twig extensions
         */
        if( Craft::$app->getRequest()->getIsSiteRequest() ){
            Craft::$app->view->registerTwigExtension(new PrismSyntaxHighlightingTwigExtension());
        }

        /**
         * Register twig variables
         */
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('syntaxHighlighting', CraftPrismSyntaxHighlightingBehavior::class);
        });

        /**
         * Prevent the Craft CMS CP version of PrismJS from loading
         */
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

        /**
         * Register field PrismJS assets
         * This is used to process queued asset files when rendering fields on the front end
         */
        Event::on(
            View::class,
            View::EVENT_END_PAGE,
            function(Event $event) {
                if( Craft::$app->getRequest()->getIsSiteRequest() ){
                    self::$plugin->prismEditorService->registerTemplateAssetFiles();
                }
            }
        );

        // Register the prism files service
        Craft::$app->setComponents([
            'prismEditorService' => Editor::class,
            'prismFilesService' => Files::class,
            'prismService' => PrismSyntaxHighlighting::class,
        ]);

        // $this->registerEditorPlugins();

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
    protected function createSettingsModel()
    {
        return new Settings();
    }

    // /**
    //  * Registers prism plugins
    //  * @author Josh Smith <josh@batch.nz>
    //  * @return void
    //  */
    // protected function registerEditorPlugins()
    // {
    //     $settings = $this->getSettings();
    //     self::$plugin->prismEditorService->registerPlugins($settings['editorPlugins']);
    // }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-prism-syntax-highlighting/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
