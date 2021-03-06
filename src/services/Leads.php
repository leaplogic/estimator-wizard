<?php

namespace leaplogic\estimatorwizard\services;

use Craft;

use leaplogic\estimatorwizard\EstimatorWizard;
use leaplogic\estimatorwizard\elements\LeadEstimate as LeadEstimateElement;
use leaplogic\estimatorwizard\events\OnBeforeSaveLeadEvent;
use leaplogic\estimatorwizard\events\OnSaveLeadEstimateEvent;
use leaplogic\estimatorwizard\models\LeadStatus;
use leaplogic\estimatorwizard\records\LeadEstimate as LeadEstimateRecord;
use leaplogic\estimatorwizard\records\LeadStatus as LeadStatusRecord;
use craft\base\Element;
use leaplogic\estimatorwizard\models\LeadEstimate;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 *
 * @property null        $defaultLeadStatusId
 * @property array       $allLeadStatuses
 */
class Leads extends Component
{

    /**
     * @var LeadEstimateRecord
     */
    protected $leadEstimateRecord;

    /**
     * @param LeadEstimateRecord $leadRecord
     */
    public function __construct($leadEstimateRecord = null)
    {
        $this->leadEstimateRecord = $leadEstimateRecord;

        if ($this->leadEstimateRecord === null) {
            $this->leadEstimateRecord = LeadEstimateRecord::find();
        }

        parent::__construct($leadEstimateRecord);
    }


    public function getLead(): LeadEstimateElement
    {
        $lead = new LeadEstimateElement();
        return $lead;
    }


    /**
     * @param $leadStatusId
     *
     * @return LeadStatus
     */
    public function getLeadStatusById($leadStatusId): LeadStatus
    {
        $leadStatus = LeadStatusRecord::find()
            ->where(['id' => $leadStatusId])
            ->one();

        $leadStatusesModel = new LeadStatus();

        if ($leadStatus) {
            $leadStatusesModel->setAttributes($leadStatus->getAttributes(), false);
        }

        return $leadStatusesModel;
    }

    /**
     * @param LeadStatus $leadStatus
     *
     * @return bool
     * @throws Exception
     */
    public function saveLeadStatus(LeadStatus $leadStatus): bool
    {
        $isNew = !$leadStatus->id;

        $record = new LeadStatusRecord();

        if ($leadStatus->id) {
            $record = LeadStatusRecord::findOne($leadStatus->id);

            if (!$record) {
                throw new Exception('No Lead Status exists with the ID: '.$leadStatus->id);
            }
        }

        $attributes = $leadStatus->getAttributes();

        if ($isNew) {
            unset($attributes['id']);
        }

        $record->setAttributes($attributes, false);

        $record->sortOrder = $leadStatus->sortOrder ?: 999;

        $leadStatus->validate();

        if (!$leadStatus->hasErrors()) {
            $transaction = Craft::$app->db->beginTransaction();

            try {
                if ($record->isDefault) {
                    LeadStatusRecord::updateAll(['isDefault' => false]);
                }

                $record->save(false);

                if (!$leadStatus->id) {
                    $leadStatus->id = $record->id;
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteLeadStatusById($id): bool
    {
        $statuses = $this->getAllLeadStatuses();

        $lead = LeadEstimateElement::find()->where(['statusId' => $id])->one();

        if ($lead) {
            return false;
        }

        if (count($statuses) >= 2) {
            $leadStatus = LeadStatusRecord::findOne($id);

            if ($leadStatus) {
                $leadStatus->delete();
                return true;
            }
        }

        return false;
    }



    /**
     * Removes a lead and related records from the database
     *
     * @param LeadElement $lead
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteLead(LeadEstimate $lead): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            // Delete the Element and Lead
            $success = Craft::$app->elements->deleteElementById($lead->id);

            if (!$success) {
                $transaction->rollBack();
                Craft::error('Couldn’t delete Lead', __METHOD__);

                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }


    /**
     * Removes leads and related records from the database given the ids
     *
     * @param $leadElements
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteLeads($leadElements): bool
    {
        foreach ($leadElements as $key => $leadElement) {
            $lead = EstimatorWizard::$app->leads->getLeadById($leadElement->id);

            if ($lead) {
                EstimatorWizard::$app->leads->deleteLead($lead);
            } else {
                Craft::error("Can't delete the lead with id: {$leadElement->id}", __METHOD__);
            }
        }

        return true;
    }

    /**
     * Reorders Lead Statuses
     *
     * @param $leadStatusIds
     *
     * @return bool
     * @throws Exception
     */
    public function reorderLeadStatuses($leadStatusIds): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            foreach ($leadStatusIds as $leadStatus => $leadStatusId) {
                $leadStatusRecord = $this->getLeadStatusRecordById($leadStatusId);

                if ($leadStatusRecord) {
                    $leadStatusRecord->sortOrder = $leadStatus + 1;
                    $leadStatusRecord->save();
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAllLeadStatuses(): array
    {
        $leadStatuses = LeadStatusRecord::find()
            ->orderBy(['sortOrder' => 'asc'])
            ->all();

        return $leadStatuses;
    }

    /**
     * Returns a lead estimate model if one is found in the database by id
     *
     * @param $leadId
     *
     * @return LeadEstimate|null
     */
    public function getLeadById($leadId)
    {
        $query = LeadEstimateElement::find();
        $query->id($leadId);

        // We are using custom statuses, so all are welcome
        $query->status(null);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->one();
    }

    /**
     * @param LeadEstimateElement $lead
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function saveLead(LeadEstimateElement $lead): bool
    {
        $isNewLead = !$lead->id;

        if ($lead->id) {
            $leadRecord = LeadEstimateRecord::findOne($lead->id);

            if (!$leadRecord) {
                throw new Exception('No lead exists with id '.$lead->id);
            }
        }

        $lead->validate();

        if ($lead->hasErrors()) {

            Craft::error($lead->getErrors(), __METHOD__);

            return false;
        }

        $event = new OnBeforeSaveLeadEvent([
            'lead' => $lead
        ]);

        $this->trigger(LeadEstimateElement::EVENT_BEFORE_SAVE, $event);

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            if (!$event->isValid || !empty($event->errors)) {
                foreach ($event->errors as $key => $error) {
                    $lead->addError($key, $error);
                }

                Craft::error('OnBeforeSaveLeadEvent is not valid', __METHOD__);

                return false;
            }

            $success = Craft::$app->getElements()->saveElement($lead);

            if (!$success) {
                Craft::error('Couldn’t save Element on saveLead service.', __METHOD__);
                $transaction->rollBack();
                return false;
            }

            Craft::info('Lead Estimate Element Saved.', __METHOD__);

            $transaction->commit();

            $this->callOnSaveLeadEstimateEvent($lead, $isNewLead);

        } catch (\Exception $e) {
            Craft::error('Failed to save element: '.$e->getMessage(), __METHOD__);
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultLeadStatusId()
    {
        $leadStatus = LeadStatusRecord::find()
            ->orderBy(['isDefault' => SORT_DESC])
            ->one();

        return $leadStatus->id ?? null;
    }


    public function getStatusByHandle($handle)
    {
        $leadStatus = LeadStatusRecord::find()
            ->where(['handle' => $handle])
            ->one();

        return $leadStatus ?? null;
    }



    /**
     * Gets an Lead Status's record.
     *
     * @param null $leadStatusId
     *
     * @return LeadStatusRecord|null|static
     * @throws Exception
     */

    public function getLeadStatusRecordById($leadStatusId = null)
    {
        if ($leadStatusId) {
            $leadStatusRecord = LeadStatusRecord::findOne($leadStatusId);

            if (!$leadStatusRecord) {
                throw new Exception('No Lead Status exists with the ID: '.$leadStatusId);
            }
        } else {
            $leadStatusRecord = new LeadStatusRecord();
        }

        return $leadStatusRecord;
    }


    /**
     * @param $lead
     * @param $isNewLead
     */
    public function callOnSaveLeadEstimateEvent($lead, $isNewLead)
    {
        $event = new OnSaveLeadEstimateEvent([
            'lead' => $lead,
            'isNewLead' => $isNewLead,
        ]);

        $this->trigger(LeadEstimateElement::EVENT_AFTER_SAVE, $event);
    }

}
