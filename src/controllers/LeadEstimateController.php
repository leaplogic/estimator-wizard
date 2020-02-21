<?php
/**
 * Estimator Wizard plugin for Craft CMS 3.x
 *
 * Manage front end estimations from wizard steps.
 *
 * @link      https://leaplogic.net
 * @copyright Copyright (c) 2020 Leap Logic
 */

namespace leaplogic\estimatorwizard\controllers;

use leaplogic\estimatorwizard\EstimatorWizard;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Leap Logic
 * @package   EstimatorWizard
 * @since     1.0.0
 */
class LeadEstimateController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'view-lead-email'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/estimator-wizard/default
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the DefaultController actionIndex() method';

        return $result;
    }


    /**
     * Handle a request going to our plugin's save-lead-estimate action URL,
     * e.g.: actions/estimator-wizard/default
     *
     * @return mixed
     */
	public function actionSaveLeadEstimate() 
	{
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        
    }


    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/estimator-wizard/default/do-something
     *
     * @return mixed
     */
    public function actionViewLeadEmail()
    {
        $result = 'Welcome to the DefaultController actionDoSomething() method';

        return $result;
    }
}
