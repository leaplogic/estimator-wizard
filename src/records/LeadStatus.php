<?php

namespace leaplogic\estimatorwizard\records;

use craft\db\ActiveRecord;
use craft\helpers\UrlHelper;

/**
 * Class LeadStatus record
 *
 * @property int    $id    ID
 * @property string $cpEditUrl
 * @property string $name  Name
 * @property string $color
 * @property int    $sortOrder
 * @property bool   $isDefault
 */
class LeadStatus extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%estimatorwizard_leadstatuses}}';
    }

    /**
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('estimator-wizard/settings/orders-statuses/'.$this->id);
    }

    /**
     * @return string
     */
    public function htmlLabel(): string
    {
        return '<span class="estimatorWizardStatusLabel"><span class="status '.$this->color.'"></span> '.$this->name.'</span>';
    }

}