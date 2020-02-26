<?php
/**
 * Estimator Wizard plugin for Craft CMS 3.x
 *
 * Manage front end estimations from wizard steps.
 *
 * @link      https://leaplogic.net
 * @copyright Copyright (c) 2020 Leap Logic
 */

namespace leaplogic\estimatorwizard\migrations;

use leaplogic\estimatorwizard\EstimatorWizard;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Estimator Wizard Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Leap Logic
 * @package   EstimatorWizard
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb();
        if ($this->createTables()) {
           // $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        //$this->driver = Craft::$app->getConfig()->getDb();
        $this->removeForiegnKeys();
        //$this->removeIndexes();
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // estimatorwizard_estimatorwizardrecord table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%estimatorwizard_estimatorwizardrecord}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%estimatorwizard_leadestimates}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'statusId' => $this->integer()->notNull(),
                    'pathLabel' => $this->string(255)->defaultValue(''),
                    'pathBasePrice' => $this->json(),
                    'results' => $this->json(),
                    'contactName' => $this->string(255)->defaultValue(''),
                    'contactEmail' => $this->string(255)->defaultValue(''),
                    'contactPhone' => $this->string(255)->defaultValue(''),
                    'contactZipCode' => $this->integer(),
                    'contactCustomer' => $this->boolean(),
                    'trafficSource' => $this->string(255)->defaultValue('organic'),
                    'notes' => $this->text(),
                ]
            );

            $this->createTable('{{%estimatorwizard_leadstatuses}}', [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'color' => $this->enum('color',
                    [
                        'green', 'orange', 'red', 'blue',
                        'yellow', 'pink', 'purple', 'turquoise',
                        'light', 'grey', 'black'
                    ])
                    ->notNull()->defaultValue('blue'),
                'sortOrder' => $this->smallInteger()->unsigned(),
                'isDefault' => $this->boolean(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createTable('{{%estimatorwizard_leadstatuslog}}', [
                'id' => $this->primaryKey(),
                'leadId' => $this->integer(),
                'status' => $this->string()->notNull(),
                'authorId' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {

    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%estimatorwizard_leadestimates}}', 'id'),
            '{{%estimatorwizard_leadestimates}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);

        // estimatorwizard_leadstatuslog table
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%estimatorwizard_leadstatuslog}}', 'leadId'
            ),
            '{{%estimatorwizard_leadstatuslog}}', 'leadId',
            '{{%estimatorwizard_leadestimates}}', 'id', 'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {

        // populate default Lead Statuses
        $defaultLeadStatuses = [
            0 => [
                'name' => 'Unverified',
                'handle' => 'unverified',
                'color' => 'blue',
                'sortOrder' => 1,
                'isDefault' => 1
            ],
            1 => [
                'name' => 'Qualified',
                'handle' => 'qualified',
                'color' => 'green',
                'sortOrder' => 2,
                'isDefault' => 0
            ],
            2 => [
                'name' => 'Qualified (Out of Area)',
                'handle' => 'qualified-out-of-area',
                'color' => 'orange',
                'sortOrder' => 3,
                'isDefault' => 0
            ],
            3 => [
                'name' => 'Unqualified (Phone)',
                'handle' => 'unqualified-phone',
                'color' => 'red',
                'sortOrder' => 4,
                'isDefault' => 0
            ],
            4 => [
                'name' => 'Unqualified (Email)',
                'handle' => 'unqualified-email',
                'color' => 'red',
                'sortOrder' => 5,
                'isDefault' => 0
            ],
            5 => [
                'name' => 'Unqualified (Spam)',
                'handle' => 'unqualified-spam',
                'color' => 'red',
                'sortOrder' => 6,
                'isDefault' => 0
            ]
        ];

        foreach ($defaultLeadStatuses as $leadStatus) {
            $this->db->createCommand()->insert('{{%estimatorwizard_leadstatuses}}', [
                'name' => $leadStatus['name'],
                'handle' => $leadStatus['handle'],
                'color' => $leadStatus['color'],
                'sortOrder' => $leadStatus['sortOrder'],
                'isDefault' => $leadStatus['isDefault']
            ])->execute();
        }

    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%estimatorwizard_leadestimates}}');
        $this->dropTableIfExists('{{%estimatorwizard_leadstatuses}}');
        $this->dropTableIfExists('{{%estimatorwizard_leadstatuslog}}');
    }

    protected function removeForiegnKeys() {
        $this->dropForeignKey($this->db->getForeignKeyName('{{%estimatorwizard_leadestimates}}', 'id'), '{{%estimatorwizard_leadestimates}}');
        $this->dropForeignKey($this->db->getForeignKeyName('{{%estimatorwizard_leadstatuslog}}', 'leadId'), '{{%estimatorwizard_leadstatuslog}}');
    }

    protected function removeIndexes() {
        
    }
}
