<?php

namespace leaplogic\estimatorwizard\integrations\sproutemail\events\notificationevents;

use barrelstrength\sproutbaseemail\base\NotificationEvent;
use leaplogic\estimatorwizard\elements\LeadEstimate;
use leaplogic\estimatorwizard\services\Leads;
use leaplogic\estimatorwizard\events\OnSaveLeadEstimateEvent;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\events\ElementEvent;
use craft\events\ModelEvent;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 *
 * @property string                            $eventHandlerClassName
 * @property LeadEstimate|null|array|ElementInterface $mockEventObject
 * @property null                              $eventObject
 * @property string                            $name
 * @property mixed                             $eventName
 * @property string                            $description
 * @property string                            $eventClassName
 */
class SaveLeadEstimateEvent extends NotificationEvent
{
    public $whenNew;

    public $whenUpdated;

    /**
     * @inheritdoc
     */
    public function getEventClassName()
    {
        return Leads::class;
    }

    /**
     * @inheritdoc
     */
    public function getEventName()
    {
        return LeadEstimate::EVENT_AFTER_SAVE;
    }

    /**
     * @inheritdoc
     */
    public function getEventHandlerClassName()
    {
        return OnSaveLeadEstimateEvent::class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('estimator-wizard', 'When a lead estimate is saved');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return Craft::t('estimator-wizard', 'Triggered when a lead estimate is saved.');
    }

    /**
     * @inheritdoc
     *
     * @param array $settings
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml($settings = []): string
    {
        return Craft::$app->getView()->renderTemplate('estimator-wizard/_integrations/sproutforms/events/notificationevents/settings', [
            'event' => $this
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getEventObject()
    {
        $event = $this->event ?? null;
        return $event->lead ?? null;
    }

    /**
     * @return array|ElementInterface|LeadEstimate|null
     */
    public function getMockEventObject()
    {
        $criteria = LeadEstimate::find();
        $criteria->orderBy(['id' => SORT_DESC]);
        $lead = $criteria->one();

        if($lead) {
            return $lead;
        }

        Craft::warning('estimator-wizard', 'Unable to generate a mock Lead Entry. Make sure you have at least one Lead Estimated submitted in your database.');

        return null;
 
    }

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [
            'whenNew', 'required', 'when' => function() {
                return $this->whenUpdated == false;
            }
        ];
        $rules[] = [
            'whenUpdated', 'required', 'when' => function() {
                return $this->whenNew == false;
            }
        ];
        $rules[] = [['whenNew', 'whenUpdated'], 'validateWhenTriggers'];
        $rules[] = [['event'], 'validateEvent'];
           
        return $rules;
    }

    public function validateWhenTriggers()
    {
        /**
         * @var ElementEvent $event
         */
        $event = $this->event ?? null;
        $isNewLead = $event->isNewLead ?? false;

        $matchesWhenNew = $this->whenNew && $isNewLead ?? false;
        $matchesWhenUpdated = $this->whenUpdated && !$isNewLead ?? false;

        if (!$matchesWhenNew && !$matchesWhenUpdated) {
            $this->addError('event', Craft::t('estimator-wizard', 'When a Lead is saved Event does not match any scenarios.'));
        }

        // Make sure new lead estimates are new.
        if (($this->whenNew && !$isNewLead) && !$this->whenUpdated) {
            $this->addError('event', Craft::t('estimator-wizard', '"When a Lead is created" is selected but the Lead is being updated.'));
        }

        // Make sure updated lead estimates are not new
        if (($this->whenUpdated && $isNewLead) && !$this->whenNew) {
            $this->addError('event', Craft::t('estimator-wizard', '"When a Lead is updated" is selected but the Lead is new.'));
        }
    }

    public function validateEvent()
    {
        //return true;
        $event = $this->event ?? null;

        // Only trigger this event when an Lead is Live.
        // When an Lead Type is updated, SCENARIO_ESSENTIALS
        // When status is disabled, SCENARIO_DEFAULT
        if ($event->lead->getScenario() !== Element::SCENARIO_LIVE) {
            $this->addError('event', Craft::t('estimator-wizard', 'The `LeadEstimateSave` Notification Event only triggers when an Lead is saved in a live scenario.'));
        }

        if (!$event) {
            $this->addError('event', Craft::t('estimator-wizard', 'ElementEvent does not exist.'));
        }

        if (get_class($event->lead) !== LeadEstimate::class) {
            $this->addError('event', Craft::t('estimator-wizard', 'The `LeadEstimateSaveNotification` Notification Event does not match the leaplogic\esitimatorwizard\element\LeadEstimate class.'));
        }
    }

}