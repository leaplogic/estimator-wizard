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
use yii\helpers\VarDumper;

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
     * @accesspublic
     */
    public $allowAnonymous = ['save-lead-estimate', 'lead-estimate-email'];

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
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('estimator-wizard');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $variables = [
            'settings' => $settings,
        ];

        return $this->renderTemplate('estimator-wizard/leads/', $variables);
    }

    /**
     * Handle a request going to our plugin's leads email view action URL,
     * e.g.: actions/estimator-wizard/lead-estimate/view-lead-email
     *
     * @return mixed
     */

    public function actionLeadEstimateEmail(string $uid = null, int $leadId = null) 
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('estimator-wizard');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $lead = null;
        if ($leadId != null) {
            $lead = EstimatorWizard::$app->leads->getLeadById($leadId);
        }

        $variables = [
            'settings' => $settings,
            'object' => $lead
        ];

        // index.twig/html used within path
        // sprout likes to use email.twig/html ¯\_(ツ)_/¯
        $templatePath = $settings->emailTemplatePath != null ? $settings->emailTemplatePath : 'estimator-wizard/_emails/';

        return $this->renderTemplate($templatePath, $variables);
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

        $settings = Craft::$app->plugins->getPlugin('estimator-wizard')->getSettings();
        $request = Craft::$app->getRequest();

        $lead = $this->getLeadModel();

        Craft::$app->getContent()->populateElementContent($lead);


        $statusId = $request->getBodyParam('statusId');

        if ($statusId !== null) {
            $lead->statusId = $statusId;
        }

        // Populate the entry with post data
        $this->populateLeadModel($lead);

        $nonWhiteListStatus = EstimatorWizard::$app->leads->getLeadStatusRecordById($settings->statusByZip);
        $zipCodeWhiteList = array_map('trim', explode(',', $settings->zipCodes));
        $inWhiteList = in_array($lead->contactZipCode, $zipCodeWhiteList);

        $lead->statusId = $lead->statusId != null
            ? $lead->statusId
            : (($lead->statusId = !$inWhiteList) 
                ? $nonWhiteListStatus->id 
                : EstimatorWizard::$app->leads->getDefaultLeadStatusId());


        $success = $lead->validate(null, false);

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

        // Save Data and Trigger the onSaveLeadEstimateEvent
        $success = EstimatorWizard::$app->leads->saveLead($lead);

        if (!$success) {
            if (Craft::$app->getRequest()->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $lead->getErrors(),
                ]);
            }
    
            return $this->redirectWithErrors($lead);
        }

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
        $this->requirePermission('estimatorWizard-editLead');

        if ($lead === null) {
            $lead = EstimatorWizard::$app->leads->getLeadById($leadId);
        }

        if (!$lead) {
            throw new NotFoundHttpException('Lead not found');
        }

        Craft::$app->getContent()->populateElementContent($lead);


        // IF Admin (return full list of statuses) ELSE (only default status & Non-Whitelist Initial Status from settings).
        $leadStatus = EstimatorWizard::$app->leads->getLeadStatusById($lead->statusId);
        $statuses = EstimatorWizard::$app->leads->getAllLeadStatuses();
        $defaultStatusId = EstimatorWizard::$app->leads->getDefaultLeadStatusId();

        $leadStatuses = [];
        //$this->requirePermission('estimatorWizard-editStatuses')
        //$this->requirePermission('admin')
        
        if(Craft::$app->getUser()->checkPermission('estimatorWizard-editLeadStatus')) {
            foreach ($statuses as $key => $status) {
                $leadStatuses[$status->id] = $status->name;
            }
        }
        else if(Craft::$app->getUser()->checkPermission('estimatorWizard-editLeadPartialStatus')) {

            $settings = Craft::$app->plugins->getPlugin('estimator-wizard')->getSettings();
            $defaultStatus = EstimatorWizard::$app->leads->getLeadStatusById($defaultStatusId);
            $nonWhiteListStatus = EstimatorWizard::$app->leads->getLeadStatusRecordById($settings->statusByZip);
            $leadStatuses[$defaultStatusId] = $defaultStatus->name;
            $leadStatuses[$nonWhiteListStatus->id] = $nonWhiteListStatus->name;
        }

        return $this->renderTemplate('estimator-wizard/leads/_edit', [
            'leadId' => $leadId,
            'leadStatus' => $leadStatus,
            'statuses' => $leadStatuses,
            'lead'=> $lead,
            'continueEditingUrl' => 'estimator-wizard/lead-estimates/edit/{id}'
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteLead(): Response
    {
        $this->requirePermission('estimatorWizard-deleteLead');

        $this->requirePostRequest();

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
        //VarDumper::dump($path, 5, true);exit;
        $lead->pathLabel = $path['label'];
        $lead->pathBasePrice = $path['price'];
        $lead->results = $request->getBodyParam('data');
        $lead->contactName = $contact['name'];
        $lead->contactEmail = $contact['email'];
        $lead->contactPhone = $contact['phone'];
        $lead->contactZipCode = $contact['zipCode'];
        $lead->contactCustomer = $contact['previousCustomer'];
        $lead->notes = $request->getBodyParam('notes');
        $lead->trafficSource = $request->getBodyParam('trafficSource');

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

        $lead = EstimatorWizard::$app->leads->getLeadById($leadId);

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
