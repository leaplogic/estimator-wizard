<?php

namespace leaplogic\estimatorwizard\records;

use craft\db\ActiveRecord;

/**
 *
 * @property $id
 * @property $leadId
 * @property $changedData
 * @property $status
 */
class LeadStatusLog extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%estimatorwizard_leadstatuslog}}';
    }
}