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
use leaplogic\estimatorwizard\elements\LeadEstimate as LeadEstimateElement;
use yii\web\Response;

use Craft;
use craft\errors\MissingComponentException;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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
    protected $allowAnonymous = ['index', 'save-lead-estimate', 'view-lead-email'];

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

        $lead = $this->getLeadModel();

        Craft::$app->getContent()->populateElementContent($lead);

        $statusId = $request->getBodyParam('statusId');

        if ($statusId !== null) {
            $lead->statusId = $statusId;
        }

        // Populate the entry with post data
        $this->populateLeadModel($lead);

        $lead->statusId = $lead->statusId != null
            ? $lead->statusId
            : EstimatorWizard::$app->leads->getDefaultLeadStatusId();


        $success = $lead->validate();

        if (!$success) {
            Craft::error($lead->getErrors(), __METHOD__);
            return $this->redirectWithErrors($lead);
        }

        return $this->saveLeadInCraft($lead);
        
    }

    /**
     * @param LeadEstimateElement $lead
     *
     * @return null|Response
     * @throws Exception
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    private function saveLeadInCraft(LeadEstimateElement $lead)
    {
        $success = true;

        // Save Data and Trigger the onSaveLeadEvent
        $success = EstimatorWizard::$app->leads->saveEntry($lead);

        if (!$success) {
            return $this->redirectWithErrors($lead);
        }

        $this->createLastLeadId($lead);

        if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson([
                'success' => true
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('estimator-wizard', 'Lead Estimate saved.'));

        return $this->redirectToPostedUrl($lead);
    }


      /**
     * Route Controller for Edit Lead Template
     *
     * @param int|null          $leadId
     * @param LeadEstimateElement|null $lead
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws MissingComponentException
     * @throws InvalidConfigException
     */
    public function actionEditLead(int $leadId = null, LeadEstimateElement $lead = null): Response
    {
        //$this->requirePermission('estimatorWizard-editLeads');

        if ($lead === null) {
            $lead = EstimatorWizard::$app->leads->getLeadById($leadId);
        }

        if (!$lead) {
            throw new NotFoundHttpException('Lead not found');
        }

        Craft::$app->getContent()->populateElementContent($lead);

        $leadStatus = EstimatorWizard::$app->leads->getLeadStatusById($lead->statusId);
        $statuses = EstimatorWizard::$app->leads->getAllLeadStatuses();
        $leadStatuses = [];

        foreach ($statuses as $key => $status) {
            $leadStatuses[$status->id] = $status->name;
        }

        $variables['leadId'] = $leadId;
        $variables['leadStatus'] = $leadStatus;
        $variables['statuses'] = $leadStatuses;

        // This is our element, so we know where to get the field values
        $variables['lead'] = $lead;

        return $this->renderTemplate('estimator-wizard/leads/_edit', $variables);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteLead(): Response
    {
        $this->requirePostRequest();
        //$this->requirePermission('estimatorWizard-editLeads');

        $request = Craft::$app->getRequest();

        // Get the Lead
        $leadId = $request->getRequiredBodyParam('leadId');

        Craft::$app->elements->deleteElementById($leadId);

        return $this->redirectToPostedUrl();
    }


    /**
     * Populate a LeadEstimateElement with post data
     *
     * @access private
     *
     * @param LeadEstimateElement $lead
     */
    private function populateLeadModel(LeadEstimateElement $lead)
    {
        $request = Craft::$app->getRequest();

        // Set the lead attributes, defaulting to the existing values for whatever is missing from the post data
        $path = $request->getBodyParam('path');
        $contact = $request->getBodyParam('contact');
        $lead->$pathLabel = $path['label'];
        $lead->$pathBasePrice = $path['price'];
        $lead->$contactName = $contact['name'];
        $lead->$contactEmail = $contact['email'];
        $lead->$contactPhone = $contact['phone'];
        $lead->$contactZipCode = $contact['zipCode'];
        $lead->$contactCustomer = $contact['previousCustomer'];

    }

    /**
     * Fetch or create a LeadEstimateElement class
     *
     * @return LeadEstimateElement
     * @throws Exception
     */

    private function getLeadModel(): LeadEstimateElement
    {
        $leadId = null;
        $request = Craft::$app->getRequest();

        /** @var EstimatorWizard $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('estimator-wizard');
        $settings = $plugin->getSettings();

        $leadId = $request->getBodyParam('leadId');

        if (!$leadId) {
            return new LeadEstimateElement();
        }

        $lead = EstimatorWizard::$app->entries->getLeadById($leadId);

        if (!$lead) {
            $message = Craft::t('estimator-wizard', 'No lead estimate exists with the given ID: {id}', [
                'leadId' => $leadId
            ]);
            throw new Exception($message);
        }

        return $lead;
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
