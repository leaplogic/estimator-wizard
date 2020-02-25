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
}
