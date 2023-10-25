<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class InsertInArchiveService
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArchivingChange[] $changes
     * @param int   $batchSize How many Items are inserted with a single query
     *
     * @return void
     * @throws Exception
     */
    public function insertInArchive(array $changes, int $batchSize = 5000): void
    {
        foreach ($changes as $entityChange) {
            $columnNames = $entityChange->getArchivedColumns();
            if (count($columnNames) === 1) {
                $columnNames = $entityChange->getClassMetaData()->getColumnNames();
            }

            $tableName = $entityChange->getArchiveTableName();

            $parts = [];
            $counter = 0;
            foreach ($entityChange->getChanges() as $change) {
                $counter++;
                $part = implode('", "', $change);
                $part = '("' . $part . '")';
                $parts[] = $part;

                if ($counter > $batchSize) {
                    $query = $this->getQuery($tableName, $columnNames, $parts);
                    $this->entityManager->getConnection()->executeQuery($query);
                    $parts = [];
                }
            }

            $query = $this->getQuery($tableName, $columnNames, $parts);
            $this->entityManager->getConnection()->executeQuery($query);
        }
    }

    private function getQuery(string $tableName, array $columnNames, array $parts): string
    {
        $valuesString = implode(', ', $parts);

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $tableName,
            implode(', ', $columnNames),
            $valuesString
        );

        return $this->removeSpecialChars($query);
    }
}