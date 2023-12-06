<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use DateTime;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Fastbolt\EntityArchiverBundle\Model\Transaction;

/**
 * Can not be replaces by using Insert and Delete because that would cause the data to be partially moved on error,
 * here we can do this in one transaction which can be reversed
 */
class MoveBetweenTablesService
{
    private EntityManagerInterface $entityManager;

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

            $tableName = $entityChange->getArchiveTableName();
            $date      = (new DateTime())->format('Y-m-d H:i:s');

            $this->entityManager->beginTransaction();

            foreach ($entityChange->getChanges() as $change) {
//                foreach ($change as &$value) {
//                    if (!$value) continue;
//                    $value = $this->removeSpecialChars($value);
//                    $value = $this->escapeQuotationMarks($value);
//                }

                $change['archived_at'] = $date;
                $this->executeQuery($tableName, $columnNames, $change, $batchSize);

            }

            $this->entityManager->commit();
        }
    }

    /**
     * @param string        $tableName
     * @param string[]      $columnNames
     * @param Transaction[] $changes
     *
     * @return void
     */
    private function executeQuery(string $tableName, array $columnNames, array $changes, int $batchSize): void
    {
        $placeholders = [];
        $ids = []; //TODO might be wrong here
        foreach ($changes as $entityChange) {
            $this->entityManager->beginTransaction();


            //TODO is this mixing up entites?
            foreach ($entityChange->getChanges() as $diff) {
                //TODO add support for other primary keys and criteria for removal
                if (!array_key_exists('id', $diff)) {
                    throw new Exception("'id' must be set as archived field");
                }

                $ids[] = $diff['id'];

                $counter = 1;
                foreach ($diff as $value) {
                    $placeholders[] = '?';
                    $type = $this->getParameterType($value);

                    $insert = sprintf(
                        'INSERT INTO %s (%s) VALUES (%s)',
                        $tableName,
                        implode(', ', $columnNames),
                        implode(', ', $placeholders)
                    );
                    $insertStmt = $this->entityManager->getConnection()->prepare($insert);
                    $insertStmt->bindValue($counter, $value, $type);
                    $insertStmt->executeStatement();
                }

                $counter++;

                if ($counter >= $batchSize) {
                    $delete = sprintf(
                        'DELETE FROM %s WHERE id IN (%s)',
                        $tableName,
                        implode(', ', $ids)
                    );
                    $deleteStmt = $this->entityManager->getConnection()->prepare($delete);
                    $deleteStmt->executeStatement();

                    $ids = [];

                    $this->entityManager->commit();
                }
            }
        }

        $this->entityManager->commit();

    }

    /**
     * @param mixed $value
     *
     * @return ParameterType
     */
    private function getParameterType($value): ParameterType
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
}
