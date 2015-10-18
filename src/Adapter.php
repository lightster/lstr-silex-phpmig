<?php

namespace Lstr\Silex\Phpmig;

use Lstr\Silex\Database\DatabaseService;
use Phpmig\Migration\Migration;
use Phpmig\Adapter\AdapterInterface;

class Adapter implements AdapterInterface
{
    /**
     * @var DatabaseService
     */
    private $connection;

    /**
     * @param DatabaseService $connection
     */
    public function __construct(DatabaseService $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $sql = <<<'SQL'
SELECT version
FROM migrations.migrations
ORDER BY version ASC
SQL;

        $all_migrations = [];
        foreach ($this->connection->query($sql) as $migration) {
            $all_migrations[] = $migration['version'];
        }

        return $all_migrations;
    }

    /**
     * @param Migration $migration
     * @return $this
     */
    public function up(Migration $migration)
    {
        $sql = <<<'SQL'
INSERT INTO migrations.migrations (version)
VALUES (:version)
SQL;
        $this->connection->query(
            $sql,
            [
                'version' => $migration->getVersion(),
            ]
        );

        return $this;
    }

    /**
     * @param Migration $migration
     * @return $this
     */
    public function down(Migration $migration)
    {
        $sql = <<<'SQL'
DELETE FROM migrations.migrations
WHERE version = :version
SQL;
        $this->connection->query(
            $sql,
            [
                'version' => $migration->getVersion(),
            ]
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSchema()
    {
        $sql = <<<'SQL'
SELECT 1
FROM pg_tables
WHERE schemaname = 'migrations'
    AND tablename = 'migrations'
SQL;

        return (bool) $this->connection->query($sql)->fetchColumn(0);
    }

    /**
     * @return $this
     */
    public function createSchema()
    {
        $sql = <<<'SQL'
CREATE SCHEMA migrations;
CREATE TABLE migrations.migrations (
    version VARCHAR(255) NOT NULL PRIMARY KEY,
    migrated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT NOW()
);
SQL;
        $this->connection->queryMultiple($sql);

        return $this;
    }
}
