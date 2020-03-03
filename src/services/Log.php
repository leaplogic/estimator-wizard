<?php

namespace leaplogic\estimatorwizard\services;

use Craft;

use leaplogic\estimatorwizard\EstimatorWizard;
use leaplogic\estimatorwizard\records\LeadStatusLog as LogRecord;
use craft\db\Query;
use craft\base\Element;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 *
 * @property null        $defaultLeadStatusId
 * @property array       $allLeadStatuses
 */
class Log extends Component
{
    public function saveLogEntry($leadId, $status, $authorId) {
        $record = new LogRecord();
        $record->leadId = $leadId;
        $record->status = $status;
        $record->authorId = $authorId;
        $record->save(false);
    }

    public function getLogById($leadId): Array
    {
        return LogRecord::find()
            ->where(['leadId' => $leadId])
            ->all();
    }

    public function getLogByStatus($leadId, $statusHandle): Array
    {
        return LogRecord::find()
            ->where(['leadId' => $leadId, 'stats' => $statusHandle])
            ->all();
    }
}