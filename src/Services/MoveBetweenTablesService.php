<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Fastbolt\EntityArchiverBundle\Model\Transaction;

/**
 * Can not be replaced by using Insert and Delete because that would cause the data to be partially moved on error,
 * here we can do this in one transaction which can be reversed
 */
class MoveBetweenTablesService
{
    private EntityManagerInterface $entityManager;

    private Connection $conn;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Transaction[] $changes
     * @param int           $batchSize
     *
     * @return void
     */
    public function moveToArchive(array $changes, int $batchSize = 1000): void
    {
        foreach ($changes as $entityChange) {
            $columnNames = $entityChange->getArchivedColumns();
            if (count($columnNames) === 1) {
                $columnNames = $entityChange->getClassMetaData()->getColumnNames();
            }

            $tableName = $entityChange->getOriginalTableName();
            $archiveName = $entityChange->getArchiveTableName();
            $date        = (new DateTime())->format('Y-m-d H:i:s');

            foreach ($entityChange->getChanges() as $change) {
//                foreach ($change as &$value) {
//                    if (!$value) continue;
//                    $value = $this->removeSpecialChars($value);
//                    $value = $this->escapeQuotationMarks($value);
//                }

                $change['archived_at'] = $date;
            }

            $this->executeQuery($tableName, $archiveName, $columnNames, $changes, $batchSize);
        }
    }

    /**
     * @param string        $tableName
     * @param string        $archiveName
     * @param string[]      $columnNames
     * @param Transaction[] $changes
     * @param int           $batchSize
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function executeQuery(
        string $tableName,
        string $archiveName,
        array $columnNames,
        array $changes,
        int $batchSize
    ): void {
        $this->conn = $this->entityManager->getConnection();
        $this->conn->beginTransaction();

        $ids = [];
        $transactionCounter = 0;
        foreach ($changes as $entityChange) {
            foreach ($entityChange->getChanges() as $diff) {
                //TODO add support for other primary keys and criteria for removal
                if (!array_key_exists('id', $diff)) {
                    throw new Exception("'id' must be set as archived field");
                }

                $ids[] = $diff['id'];

                $placeholders = [];
                foreach ($diff as $value) {
                    $placeholders[] = '?';
                }

                $insert = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $archiveName,
                    implode(', ', $columnNames),
                    implode(', ', $placeholders)
                );
                $insertStmt = $this->conn->prepare($insert);

                $argumentCounter = 1;
                foreach ($diff as $value) {
                    $type = $this->getParameterType($value);
                    $insertStmt->bindValue($argumentCounter, $value, $type);
                    $argumentCounter++;
                }

                $insertStmt->executeStatement();

                if ($transactionCounter >= $batchSize) {
                    $this->executeDelete($tableName, $ids);

                    $ids = [];

                    $this->conn->commit();
                    $this->conn->beginTransaction();
                    $transactionCounter = 0;
                }
                $transactionCounter++;
            }
        }

        if ($this->conn->isTransactionActive()) {
            if (count($ids) > 0) {
                $this->executeDelete($tableName, $ids);
            }
            $this->conn->commit();
        }
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    private function getParameterType($value): int
    {
        $type = ParameterType::STRING;
        if (is_int($value)) {
            $type = ParameterType::INTEGER;
        }

        if (is_null($value)) {
            $type = ParameterType::NULL;
        }

        return $type;
    }

    /**
     * @param string                    $tableName
     * @param array                     $ids
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function executeDelete(string $tableName, array $ids): void
    {
        $delete     = sprintf(
            'DELETE FROM %s WHERE id IN (%s)',
            $tableName,
            implode(', ', $ids)
        );
        $deleteStmt = $this->conn->prepare($delete);
        $deleteStmt->executeStatement();
    }
}
