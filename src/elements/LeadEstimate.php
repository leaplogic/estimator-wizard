<?php
/**
 * Estimator Wizard plugin for Craft CMS 3.x
 *
 * Manage front end estimations from wizard steps.
 *
 * @link      https://leaplogic.net
 * @copyright Copyright (c) 2020 Leap Logic
 */

namespace leaplogic\estimatorwizard\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;

use craft\helpers\UrlHelper;
use craft\elements\actions\Delete;
use leaplogic\estimatorwizard\elements\db\LeadEstimateQuery;
use leaplogic\estimatorwizard\records\LeadEstimate as LeadEstimateRecord;
use leaplogic\estimatorwizard\EstimatorWizard;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 *  Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    Leap Logic
 * @package   EstimatorWizard
 * @since     1.0.0
 */
class LeadEstimate extends Element
{
    // Public Properties
    // =========================================================================

    /**
     * Path
     *
     * @var string
     */
    public $id;
    public $pathLabel;
    public $pathBasePrice;
    public $results;
    public $contactName;
    public $contactEmail;
    public $contactPhone;
    public $contactZipCode;
    public $contactCustomer;
    public $statusId;
    public $statusHandle;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('estimator-wizard', 'Lead Estimates');
    }


    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'leads';
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type have statuses.
     *
     * If this returns `true`, the element index template will show a Status menu
     * by default, and your elements will get status indicator icons next to them.
     *
     * Use [[statuses()]] to customize which statuses the elements might have.
     *
     * @return bool Whether elements of this type have statuses.
     * @see statuses()
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'estimator-wizard/leads/edit/'.$this->id
        );
    }


    public static function statuses(): array
    {
        $statuses = EstimatorWizard::$app->leads->getAllLeadStatuses();
        $statusArray = [];

        foreach ($statuses as $status) {
            $key = $status['handle'];
            $statusArray[$key] = [
                'label' => $status['name'],
                'color' => $status['color'],
            ];
        }

        return $statusArray;
       /* return [
            // 'unverified' => ['label' => \Craft::t('estimator-wizard', 'Unverified'), 'color' => '333333'],
            // 'qualified' => ['label' => \Craft::t('estimator-wizard', 'Qualified'), 'color' => 'A8E26E'],
            // 'qalified-out-of-area' => ['label' => \Craft::t('estimator-wizard', 'Qualified (Out of Area)'), 'color' => 'cb7624'],
            // 'unqualified-phone' => ['label' => \Craft::t('estimator-wizard', 'Unqualified (Phone)'), 'color' => 'a5272a'],
            // 'unqualified-email' => ['label' => \Craft::t('estimator-wizard', 'Unqualified (Email)'), 'color' => 'a5272a'],
            // 'unqualified-spam' => ['label' => \Craft::t('estimator-wizard', 'Unqualified (Spam)'), 'color' => 'a5272a'],
        ];*/
    }

    /**
     *
     * @return string|null
     */
    public function getStatus():string
    {
        $statusId = $this->statusId;

        return EstimatorWizard::$app->leads->getLeadStatusById($statusId)->handle;
    }


    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     *
     * The returned [[ElementQueryInterface]] instance can be further customized by calling
     * methods defined in [[ElementQueryInterface]] before `one()` or `all()` is called to return
     * populated [[ElementInterface]] instances. For example,
     *
     * ```php
     * // Find the entry whose ID is 5
     * $entry = Entry::find()->id(5)->one();
     *
     * // Find all assets and order them by their filename:
     * $assets = Asset::find()
     *     ->orderBy('filename')
     *     ->all();
     * ```
     *
     * If you want to define custom criteria parameters for your elements, you can do so by overriding
     * this method and returning a custom query class. For example,
     *
     * ```php
     * class Product extends Element
     * {
     *     public static function find()
     *     {
     *         // use ProductQuery instead of the default ElementQuery
     *         return new ProductQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * You can also set default criteria parameters on the ElementQuery if you don’t have a need for
     * a custom query class. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->limit(50);
     *     }
     * }
     * ```
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new ElementQuery(get_called_class());
    }


    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }



    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * Returns the HTML for the element’s editor HUD.
     *
     * @return string The HTML for the editor HUD
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }


    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes['pathLabel'] = ['label' => Craft::t('estimator-wizard', 'Path Label')];
        $attributes['contactName'] = ['label' => Craft::t('estimator-wizard', 'Contact Name')];
        //$attributes['contactEmail'] = ['label' => Craft::t('estimator-wizard', 'Contact Email')];
        $attributes['dateCreated'] = ['label' => Craft::t('estimator-wizard', 'Date Created')];
        $attributes['dateUpdated'] = ['label' => Craft::t('estimator-wizard', 'Date Updated')];

        return $attributes;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        // Get the lead estimate record
        if (!$isNew) {
            $record = LeadEstimateRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Lead ID: '.$this->id);
            }
        } else {
            $record = new LeadEstimateRecord();
            $record->id = $this->id;
        }

        $record->statusId = $this->statusId;
        $record->pathLabel = $this->pathLabel;
        $record->pathBasePrice = $this->pathBasePrice;
        $record->results = $this->results;
        $record->contactName = $this->contactName;
        $record->contactEmail = $this->contactEmail;
        $record->contactPhone = $this->contactPhone;
        $record->contactZipCode = $this->contactZipCode;
        $record->contactCustomer = $this->contactCustomer;

        $record->save(false);

        parent::afterSave($isNew);
    }


    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('estimator-wizard', 'Are you sure you want to delete the selected leads?'),
            'successMessage' => Craft::t('estimator-wizard', 'Leads deleted.'),
        ]);

        return $actions;
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete()
    {
    }

}
