<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use DateTime;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class InsertInArchiveService
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Transaction[] $changes
     * @param int           $batchSize How many Items are inserted with a single query
     *
     * @return void
     */
    public function insertInArchive(array $changes, int $batchSize = 5000): void
    {
        foreach ($changes as $entityChange) {
            $columnNames = $entityChange->getArchivedColumns();
            if (count($columnNames) === 1) {
                $columnNames = $entityChange->getClassMetaData()->getColumnNames();
            }

            $tableName = $entityChange->getArchiveTableName();
            $date      = (new DateTime())->format('Y-m-d H:i:s');

            $this->entityManager->beginTransaction();

            $counter = 0;
            foreach ($entityChange->getChanges() as $change) {
//                foreach ($change as &$value) {
//                    if (!$value) continue;
//                    $value = $this->removeSpecialChars($value);
//                    $value = $this->escapeQuotationMarks($value);
//                }

                $change[$entityChange->getArchivedAtFieldName()] = $date;
                $this->executeQuery($tableName, $columnNames, $change);

                $counter++;
                if ($counter >= $batchSize) {
                    $this->entityManager->commit();
                    $this->entityManager->beginTransaction();
                    $counter = 0;
                }
            }

            $this->entityManager->commit();
        }
    }

    /**
     * @param string $tableName
     * @param array  $columnNames
     * @param array  $change
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function executeQuery(string $tableName, array $columnNames, array $change): void
    {
        $placeholders = [];
        foreach ($change as $value) {
            $placeholders[] = '?';
        }

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            implode(', ', $columnNames),
            implode(', ', $placeholders)
        );

        $stmt = $this->entityManager->getConnection()->prepare($query);

        $counter = 1;
        foreach ($change as $value) {
            $type = ParameterType::STRING;
            if (is_int($value)) {
                $type = ParameterType::INTEGER;
            }

            if (is_null($value)) {
                $type = ParameterType::NULL;
            }

            $stmt->bindValue($counter, $value, $type);
            $counter++;
        }

        $stmt->executeStatement();
    }
}
