<?php

require_once dirname(__DIR__).'/../vendor/autoload.php';
require_once dirname(__DIR__).'/generated-conf/config.php';

use Propel\Generator\Manager\MigrationManager;
use App\Models\User;
use App\Models\UserQuery;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1590212123.
 * Generated on 2020-05-23 05:35:23 by kairat
 */
class PropelMigration_1590212123
{
    public $comment = '';

    public function preUp(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postUp(MigrationManager $manager)
    {
        $user = new User;
        $user->setUsername('admin');
        $user->setPassword(password_hash('123', PASSWORD_DEFAULT));
        $user->save();
    }

    public function preDown(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postDown(MigrationManager $manager)
    {
        $user = UserQuery::create()->findByUsername('admin');
        $user->delete();
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
            'default' => '',
        );
    }

}