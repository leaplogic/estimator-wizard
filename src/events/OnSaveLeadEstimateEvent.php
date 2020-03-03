<?php

namespace leaplogic\estimatorwizard\events;

use leaplogic\estimatorwizard\elements\LeadEstimate as Lead;
use yii\base\Event;

/**
 * OnSaveLeadEstimateEvent class.
 */
class OnSaveLeadEstimateEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Lead
     */
    public $lead;

    /**
     * @var bool
     */
    public $isNewLead = true;
}
