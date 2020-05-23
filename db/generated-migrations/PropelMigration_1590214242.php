<?php

require_once dirname(__DIR__).'/../vendor/autoload.php';
require_once dirname(__DIR__).'/generated-conf/config.php';

use Propel\Generator\Manager\MigrationManager;
use Faker\Factory as FakerFactory;
use App\Models\Task;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1590214242.
 * Generated on 2020-05-23 06:10:42 by kairat
 */
class PropelMigration_1590214242
{
    public $comment = '';

    public function preUp(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postUp(MigrationManager $manager)
    {
        $faker = FakerFactory::create();

        for ($i = 1; $i <= 10; $i++) {
            $task = new Task;
            $task->setUsername($faker->userName);
            $task->setEmail($faker->email);
            $task->setContent($faker->sentences(2, true));
            $task->save();
        }
    }

    public function preDown(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postDown(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array(
            'default' => '',
        );
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array(
            'default' => 'TRUNCATE TABLE `tasks`'
        );
    }

}