<?php

namespace leaplogic\estimatorwizard\events;

use yii\base\Event;
use leaplogic\estimatorwizard\elements\LeadEstimate;

/**
 * OnBeforeSaveLeadEvent class.
 */
class OnBeforeSaveLeadEvent extends Event
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var LeadEstimate
     */
    public $lead;

    /**
     * @var bool
     */
    public $isValid = true;

    /**
     * @var bool
     */
    public $fakeIt = false;
}
