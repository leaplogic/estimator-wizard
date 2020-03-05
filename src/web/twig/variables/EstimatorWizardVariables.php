<?php

namespace leaplogic\estimatorwizard\web\twig\variables;

use leaplogic\estimatorwizard\elements\db\LeadEstimateQuery;
use leaplogic\estimatorwizard\elements\LeadEstimate;
use leaplogic\estimatorwizard\services\Leads;
use leaplogic\estimatorwizard\EstimatorWizard;
use leaplogic\estimatorwizard\records\LeadStatus as LeadStatusRecord;
use Craft;
use craft\base\ElementInterface;
use craft\errors\MissingComponentException;
use craft\helpers\Template as TemplateHelper;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\web\BadRequestHttpException;


class EstimatorWizardVariables
{
    /**
     * @return string
     */
    public function getName(): string
    {
        /** @var EstimatorWizard $plugin */
        $plugin = Craft::$app->plugins->getPlugin('estimator-wizard');

        return $plugin->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        /** @var EstimatorWizard $plugin */
        $plugin = Craft::$app->plugins->getPlugin('estimator-wizard');

        return $plugin->getVersion();
    }


    /**
     * Gets a specific lead If no lead is found, returns null
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getLeadById($id)
    {
        return EstimatorWizard::$app->leads->getLeadById($id);
    }

    /**
     * Get all forms
     *
     * @return array
     */
    public function getAllLeads(): array
    {
        return EstimatorWizard::$app->leads->getAllLeads();
    }



    /**
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin): bool
    {
        $plugins = Craft::$app->plugins->getAllPlugins();

        if (array_key_exists($plugin, $plugins)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getStatuses(): array
    {
        return EstimatorWizard::$app->leads->getAllLeadStatuses();
    }

    public function getStatusesAsOptions(): array
    {
        $options = [];
        foreach (EstimatorWizard::$app->leads->getAllLeadStatuses() as $value) {
            array_push(
                $options, 
                [
                    "label" => $value['name'],
                    "value" => $value['id'],
                ]);
        }
        return $options;
    }

    public function getStatusByHandle($handle): object
    {
        return EstimatorWizard::$app->leads->getStatusByHandle($handle);
    }

    public function getStatusById($id): object
    {
        return LeadStatusRecord::find()->id($id)->one();
    }

    /**
     * Returns a new LeadEstimateQuery instance.
     *
     * @param mixed $criteria
     *
     * @return LeadEstimateQuery
     */
    public function leads($criteria = null): LeadEstimateQuery
    {
        $query = LeadEstimate::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

    


    public function leadLog($leadId): Array
    {
        return EstimatorWizard::$plugin->getInstance()->log->getLogById($leadId);
    }


}