<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class InsertInArchiveService
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function insertInArchive(array $changes): void
    {
        foreach ($changes as $entityChange) {
            $columnNames = $entityChange->getArchivedColumns();
            if (count($columnNames) === 1) {
                $columnNames = $entityChange->getClassMetaData()->getColumnNames();
            }

            $parts = [];
            foreach ($entityChange->getChanges() as $change) {
                $part = implode('", "', $change);
                $part = '("' . $part . '")';
                $parts[] = $part;
            }

            $valuesString = implode(', ', $parts);

            $query = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $entityChange->getArchiveTableName(),
                implode(', ', $columnNames),
                $valuesString
            );

            $query = $this->removeSpecialChars($query);

            $this->entityManager->getConnection()->executeQuery($query);
        }
    }
}