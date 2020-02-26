<?php
/**
 * Estimator Wizard plugin for Craft CMS 3.x
 *
 * Manage front end estimations from wizard steps.
 *
 * @link      https://leaplogic.net
 * @copyright Copyright (c) 2020 Leap Logic
 */

namespace leaplogic\estimatorwizard;

use leaplogic\estimatorwizard\services\App;
use leaplogic\estimatorwizard\models\Settings as SettingsModel;
use leaplogic\estimatorwizard\elements\LeadEstimate;
use leaplogic\estimatorwizard\web\twig\variables\EstimatorWizardVariables;
use leaplogic\estimatorwizard\events\OnSaveLeadEvent;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Leap Logic
 * @package   EstimatorWizard
 * @since     1.0.0
 *
 */
class EstimatorWizard extends Plugin
{
    // Static Properties
    // =========================================================================
    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * EstimatorWizard::$plugin
     *
     * @var EstimatorWizard
     */
    public static $plugin;

    /**
     * Enable use of EstimatorWizard::$app-> in place of Craft::$app->
     *
     * @var App
     */
    public static $app;

    // Public Properties
    // =========================================================================

    public static $pluginHandle = 'estimator-wizard';

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================


    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@estimatorwizard', $this->basePath);


        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['estimator-wizard'] = 'estimator-wizard/lead-estimate/index';
                $event->rules['estimator-wizard/lead-estimates/edit/<leadId:\d+>'] = "estimator-wizard/lead-estimate/edit-lead";
                $event->rules['estimator-wizard/lead-estimates/new'] = "estimator-wizard/lead-estimate/edit-lead";
                $event->rules['estimator-wizard/settings/general'] = 'estimator-wizard/settings/index';
                $event->rules['estimator-wizard/settings/lead-statuses'] = 'estimator-wizard/lead-statuses/index';
                $event->rules['estimator-wizard/settings/lead-statuses/new'] = "estimator-wizard/lead-statuses/edit";
                $event->rules['estimator-wizard/settings/lead-statuses/<leadStatusId:\d+>'] = 'estimator-wizard/lead-statuses/edit';

            }
        );

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('estimatorWizard', EstimatorWizardVariables::class);
        });

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LeadEstimate::class;
            }
        );

        $this->setComponents([
            'leads' => \leaplogic\estimatorwizard\services\Leads::class,
            'settings' => \leaplogic\estimatorwizard\services\Settings::class,
            'log' => \leaplogic\estimatorwizard\services\Log::class,
        ]);


/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        // Craft::info(
        //     Craft::t(
        //         'estimator-wizard',
        //         '{name} plugin loaded',
        //         ['name' => 'Estimator Wizard']
        //     ),
        //     __METHOD__
        // );
    }


        /**
     * @return array|null
     */
    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        $parent['label'] = "Estimator Wizard";

        
        $parent['subnav']['leads'] = [
            'label' => Craft::t('estimator-wizard', 'Lead Estimates'),
            'url' => 'estimator-wizard/'
        ];
        

        if (Craft::$app->getUser()->getIsAdmin()) {
            $parent['subnav']['settings'] = [
                'label' => Craft::t('estimator-wizard', 'Settings'),
                'url' => 'estimator-wizard/settings/general'
            ];
        }

        return $parent;
    }

    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('estimator-wizard/settings/general');

        return Craft::$app->getResponse()->redirect($url);
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new SettingsModel();
    }
    

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'estimator-wizard/settings/general',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
    
}
