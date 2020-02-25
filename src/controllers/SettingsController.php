<?php

namespace leaplogic\estimatorwizard\controllers;

use Craft;
use leaplogic\estimatorwizard\EstimatorWizard;
use leaplogic\estimatorwizard\models\LeadStatus;
use leaplogic\estimatorwizard\services\Settings;
use craft\helpers\Json;
use craft\web\Controller as BaseController;
use Exception;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\VarDumper;

class SettingsController extends BaseController
{
    public function actionIndex()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('estimator-wizard');

        return $this->renderTemplate('estimator-wizard/settings/general', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings()
        ]);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionSaveSettings()
    { 
        $this->requirePostRequest();
        $plugin = Craft::$app->getPlugins()->getPlugin('estimator-wizard');
        // the submitted settings
        $settingsModel = null;
        $postSettings = Craft::$app->getRequest()->getBodyParam('settings');

        $settings = EstimatorWizard::getInstance()->settings->saveSettings($plugin, $postSettings);

        if ($settings->hasErrors()) {
            Craft::$app->getSession()->setError(Craft::t('estimator-wizard', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings
            ]);

            return null;
        }
        

        Craft::$app->getSession()->setNotice(Craft::t('estimator-wizard', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
