<?php

namespace leaplogic\estimatorwizard\models;

use leaplogic\estimatorwizard\EstimatorWizard;
use craft\base\Model;
use Craft;
use craft\errors\MissingComponentException;
use yii\base\InvalidConfigException;

class LeadStatusLog extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null
     */
    public $leadId;

    /**
     * @var string
     */
    public $status;

    /**
     * @var int|null
     */
    public $authorId;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('estimator-wizard', $this->id);
    }

}