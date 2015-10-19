<?php

namespace Lstr\Silex\Phpmig;

use Lstr\Silex\Database\DatabaseService;
use Phpmig\Migration\Migration as PhpmigMigration;

abstract class TransactionalMigration extends PhpmigMigration
{
    public function up()
    {
        $db = $this->getDatabaseService();
        $db->query('BEGIN');
        $this->transactionalUp($db);
        $db->query('COMMIT');
    }

    public function down()
    {
        $db = $this->getDatabaseService();
        $db->query('BEGIN');
        $this->transactionalDown($db);
        $db->query('COMMIT');
    }

    /**
     * @return DatabaseService
     */
    protected function getDatabaseService()
    {
        $container = $this->getContainer();
        return $container['lstr.phpmig.db_service'];
    }

    /**
     * @param DatabaseService $db
     */
    abstract protected function transactionalUp(DatabaseService $db);

    /**
     * @param DatabaseService $db
     */
    abstract protected function transactionalDown(DatabaseService $db);
}
