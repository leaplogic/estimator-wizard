<?php
/**
 * Estimator Wizard plugin for Craft CMS 3.x
 *
 * Manage front end estimations from wizard steps.
 *
 * @link      https://leaplogic.net
 * @copyright Copyright (c) 2020 Leap Logic
 */

namespace leaplogic\estimatorwizard\models;

use leaplogic\estimatorwizard\EstimatorWizard;

use Craft;
use craft\base\Model;

/**
 * EstimatorWizard Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Leap Logic
 * @package   EstimatorWizard
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Email To
     *
     * @var string
     */
    public $emailTo;

    /**
     * Email Template Path
     *
     * @var string
     */
    public $emailTemplatePath;


    /**
     * Zipcodes
     *
     * @var string
     */
    public $zipCodes;

    /**
     * StatusByZip
     *
     * @var string
     */
    public $statusByZip;

}
