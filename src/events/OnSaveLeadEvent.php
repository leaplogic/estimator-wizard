<?php

namespace leaplogic\estimatorwizard\events;

use leaplogic\estimatorwizard\elements\LeadEstimate;
use yii\base\Event;

/**
 * OnSaveLeadEvent class.
 */
class OnSaveLeadEvent extends Event
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
