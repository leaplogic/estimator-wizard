<?php

namespace leaplogic\estimatorwizard\elements\db;

use leaplogic\estimatorwizard\elements\LeadEstimate;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use leaplogic\estimatorwizard\EstimatorWizard;
use yii\base\InvalidConfigException;

class LeadEstimateQuery extends ElementQuery
{
    /**
     * @var int
     */
    public $statusId;

    /**
     * @var string
     */
    public $statusHandle;

    public $status = [];

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'estimatorwizard_leadestimates.id';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[statusId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function statusId($value): LeadEstimateQuery
    {
        $this->statusId = $value;

        return $this;
    }



    /**
     * Sets the [[statusHandle]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function statusHandle($value): LeadEstimateQuery
    {
        $this->statusHandle = $value;

        return $this;
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('estimatorwizard_leadestimates');


        $this->query->select([
            'estimatorwizard_leadestimates.statusId',
            'estimatorwizard_leadestimates.dateCreated',
            'estimatorwizard_leadestimates.dateUpdated',
            'estimatorwizard_leadestimates.uid',
            'estimatorwizard_leadstatuses.handle as statusHandle'
        ]);

        

        $this->query->innerJoin('{{%estimatorwizard_leadstatuses}} estimatorwizard_leadstatuses', '[[estimatorwizard_leadstatuses.id]] = [[estimatorwizard_leadestimates.statusId]]');
        $this->subQuery->innerJoin('{{%estimatorwizard_leadstatuses}} estimatorwizard_leadstatuses', '[[estimatorwizard_leadstatuses.id]] = [[estimatorwizard_leadestimates.statusId]]');

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'estimatorwizard_leadestimates.id', $this->id)
            );
        }

        if ($this->statusId) {
            $this->subQuery->andWhere(Db::parseParam(
                'estimatorwizard_leadestimates.statusId', $this->statusId)
            );
        }

        if ($this->statusHandle) {
            $this->subQuery->andWhere(Db::parseParam(
                'estimatorwizard_leadstatuses.handle', $this->statusHandle)
            );
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritDoc
     */
    protected function statusCondition(string $status)
    {
        return Db::parseParam('estimatorwizard_leadstatuses.handle', $status);
    }

}
