<?php

namespace Fastbolt\EntityArchiverBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Migrations\Event\MigrationsEventArgs;
use Doctrine\Migrations\Events;
class MigrationsListener implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::onMigrationsMigrating,
        ];
    }

    public function onMigrationsMigrating(MigrationsEventArgs $args): void
    {
        $test = $args;
    }

//    public function onMigrationsGenerate(MigrationsGenerateEvent $event)
//    {
//        $excludedTables = [];
//
//        // Add tables you want to exclude from migrations
//        $excludedTables[] = 'your_table_name_archive';
//        // Add more tables if needed
//
//        $migrations = $event->getMigrations();
//
//        foreach ($migrations as $migration) {
//            $tableName = $migration->getUp()->getSql()[0]->getTableName();
//
//            if (in_array($tableName, $excludedTables)) {
//                // Remove the migration for the excluded table
//                $event->removeMigration($migration);
//            }
//        }
//    }
}