<?php
/**
 * @link      https://leaplogic.com
 * @copyright Copyright (c) Leap Logic
 * @license   https://craftcms.github.io/license
 */

namespace leaplogic\estimatorwizard\elements\actions;

use leaplogic\estimatorwizard\EstimatorWizard;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use Throwable;

/**
 *
 * @property string $triggerLabel
 */
class Delete extends ElementAction
{
    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('estimator-wizard', 'Delete…');
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('estimator-wizard', 'Are you sure you want to delete the selected leads?');
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = EstimatorWizard::$app->leads->deleteLeads($query->all());

        if ($response) {
            $message = Craft::t('estimator-wizard', 'Leads Deleted.');
        } else {
            $message = Craft::t('estimator-wizard', 'Failed to delete Leads.');
        }

        $this->setMessage($message);

        return $response;
    }
}
