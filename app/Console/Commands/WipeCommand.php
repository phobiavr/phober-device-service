<?php

namespace App\Console\Commands;


use Illuminate\Database\Schema\MySqlBuilder;

class WipeCommand extends \Illuminate\Database\Console\WipeCommand
{
    /**
     * Drop all the database tables.
     *
     * @param string $database
     * @return void
     */
    protected function dropAllTables($database): void
    {
        /** @var MySqlBuilder $schema */
        $schema = $this->laravel['db']->connection($database)->getSchemaBuilder();
        $tables = [];
        $excludedTables = ['devices', 'device_instances', 'game_device', 'games', 'game_genre', 'genres'];

        foreach ($schema->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        $tablesToDrop = array_filter($tables, function ($tableName) use ($excludedTables) {
            return !in_array($tableName, $excludedTables);
        });

        if (empty($tablesToDrop)) {
            return;
        }

        $schema->disableForeignKeyConstraints();

        foreach ($tablesToDrop as $table) {
            $schema->drop($table);
        }

        $schema->enableForeignKeyConstraints();
    }
}
