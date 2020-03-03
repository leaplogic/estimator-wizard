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

    public $pathLabel;
    public $pathBasePrice;
    public $contactName;
    public $contactEmail;
    public $contactPhone;
    public $results;
    public $contactZipCode;
    public $contactCustomer;
    public $trafficSource;
    public $notes;
    

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
     * Sets the [[pathLabel]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function pathLabel($value): LeadEstimateQuery
    {
        $this->pathLabel = $value;

        return $this;
    }


    /**
     * Sets the [[pathBasePrice]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function pathBasePrice($value): LeadEstimateQuery
    {
        $this->pathBasePrice = $value;

        return $this;
    }


    /**
     * Sets the [[results]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function results($value): LeadEstimateQuery
    {
        $this->results = $value;

        return $this;
    }


    /**
     * Sets the [[contactName]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function contactName($value): LeadEstimateQuery
    {
        $this->contactName = $value;

        return $this;
    }


    /**
     * Sets the [[contactEmail]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function contactEmail($value): LeadEstimateQuery
    {
        $this->contactEmail = $value;

        return $this;
    }


    /**
     * Sets the [[contactPhone]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function contactPhone($value): LeadEstimateQuery
    {
        $this->contactPhone = $value;

        return $this;
    }

    /**
     * Sets the [[contactZipCode]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function contactZipCode($value): LeadEstimateQuery
    {
        $this->contactZipCode = $value;

        return $this;
    }


    /**
     * Sets the [[contactCustomer]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function contactCustomer($value): LeadEstimateQuery
    {
        $this->contactCustomer = $value;

        return $this;
    }


    /**
     * Sets the [[notes]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function notes($value): LeadEstimateQuery
    {
        $this->notes = $value;

        return $this;
    }

     /**
     * Sets the [[trafficSource]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function trafficSource($value): LeadEstimateQuery
    {
        $this->trafficSource = $value;

        return $this;
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
            'estimatorwizard_leadestimates.pathLabel',
            'estimatorwizard_leadestimates.pathBasePrice',
            'estimatorwizard_leadestimates.results',
            'estimatorwizard_leadestimates.contactName',
            'estimatorwizard_leadestimates.contactEmail',
            'estimatorwizard_leadestimates.contactPhone',
            'estimatorwizard_leadestimates.contactZipCode',
            'estimatorwizard_leadestimates.contactCustomer',
            'estimatorwizard_leadestimates.statusId',
            'estimatorwizard_leadestimates.dateCreated',
            'estimatorwizard_leadestimates.dateUpdated',
            'estimatorwizard_leadestimates.uid',
            'estimatorwizard_leadestimates.notes',
            'estimatorwizard_leadestimates.trafficSource',
            'estimatorwizard_leadstatuses.handle as statusHandle'
        ]);

        $this->query->innerJoin('{{%estimatorwizard_leadstatuses}} estimatorwizard_leadstatuses', '[[estimatorwizard_leadstatuses.id]] = [[estimatorwizard_leadestimates.statusId]]');
        $this->subQuery->innerJoin('{{%estimatorwizard_leadstatuses}} estimatorwizard_leadstatuses', '[[estimatorwizard_leadstatuses.id]] = [[estimatorwizard_leadestimates.statusId]]');
       
        //$this->subQuery->innerJoin('{{%estimatorwizard_leadstatuslog}} estimatorwizard_leadstatuslog', '[[estimatorwizard_leadstatuslog.leadId]] = [[estimatorwizard_leadestimates.id]]');

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

        if ($this->contactZipCode) {
            $this->subQuery->andWhere(Db::parseParam(
                'estimatorwizard_leadstatuses.contactZipCode', $this->contactZipCode)
            );
        }

        if ($this->contactCustomer) {
            $this->subQuery->andWhere(Db::parseParam(
                'estimatorwizard_leadstatuses.contactCustomer', $this->contactCustomer)
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
