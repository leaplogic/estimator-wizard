<?php

namespace leaplogic\estimatorwizard\controllers;

use Craft;
use leaplogic\estimatorwizard\EstimatorWizard;
use leaplogic\estimatorwizard\models\LeadStatus;
use craft\helpers\Json;
use craft\web\Controller as BaseController;
use Exception;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class LeadStatusesController extends BaseController
{


    public function actionIndex()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('estimator-wizard');
        $leadStatuses = EstimatorWizard::$app->leads->getAllLeadStatuses();

        return $this->renderTemplate('estimator-wizard/settings/leadstatuses/index', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings(),
            'leadStatuses' => $leadStatuses
        ]);
    }


    /**
     * @param int|null         $leadStatusId
     * @param LeadStatus|null $leadStatus
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit(int $leadStatusId = null, LeadStatus $leadStatus = null): Response
    {
        $this->requireAdmin();

        if (!$leadStatus) {
            if ($leadStatusId) {
                $leadStatus = EstimatorWizard::$app->leads->getLeadStatusById($leadStatusId);

                if (!$leadStatus->id) {
                    throw new NotFoundHttpException('Lead Status not found');
                }
            } else {
                $leadStatus = new LeadStatus();
            }
        }

        return $this->renderTemplate('estimator-wizard/settings/leadstatuses/_edit', [
            'leadStatus' => $leadStatus,
            'leadStatusId' => $leadStatusId
        ]);
    }

    /**
     * @return null|Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $id = Craft::$app->request->getBodyParam('leadStatusId');
        $leadStatus = EstimatorWizard::$app->leads->getLeadStatusById($id);

        $leadStatus->name = Craft::$app->request->getBodyParam('name');
        $leadStatus->handle = Craft::$app->request->getBodyParam('handle');
        $leadStatus->color = Craft::$app->request->getBodyParam('color');
        $leadStatus->isDefault = Craft::$app->request->getBodyParam('isDefault');

        if (!EstimatorWizard::$app->leads->saveLeadStatus($leadStatus)) {
            Craft::$app->session->setError(Craft::t('estimator-wizard', 'Could not save Lead Status.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'leadStatus' => $leadStatus
            ]);

            return null;
        }

        Craft::$app->session->setNotice(Craft::t('sprout-forms', 'Lead Status saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $ids = Json::decode(Craft::$app->request->getRequiredBodyParam('ids'));

        if ($success = EstimatorWizard::$app->leads->reorderLeadStatuses($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => Craft::t('estimator-wizard', "Couldn't reorder Order Statuses.")]);
    }

    /**
     * @return Response
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     * @throws BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $leadStatusId = Craft::$app->request->getRequiredBodyParam('id');

        if (!EstimatorWizard::$app->leads->deleteLeadStatusById($leadStatusId)) {
            $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }
}
