<?php

namespace leaplogic\estimatorwizard\models;

use craft\base\Model;
use Craft;

class LeadEstimate extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string
     */
    public $statusId;

    /**
     * @var string
     */
    public $pathLabel;

    /**
     * @var text
     */
    public $pathBasePrice;

    /**
     * @var text
     */
    public $results;

    /**
     * @var string
     */
    public $contactName;

      /**
     * @var string
     */
    public $contactEmail;

      /**
     * @var string
     */
    public $contactPhone;

      /**
     * @var string
     */
    public $contactZipCode;

      /**
     * @var bool
     */
    public $contactCustomer;

     /**
     * @var text
     */
    public $notes;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('estimator-wizard', $this->name);
    }


    /**
     * @inheritdoc
     */
    /*public function rules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255]
        ];
    }*/
}