<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class DeleteService
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
     * @param Transaction[] $transactions
     *
     * @return void
     */
    public function deleteFromOriginTable(array $transactions): void
    {
        foreach ($transactions as $change) {
            $metaData  = $this->entityManager->getClassMetadata($change->getClassname());
            $tableName = $metaData->getTableName();

            $ids = [];
            foreach ($change->getChanges() as $diff) {
                //TODO add support for other primary keys and criteria for removal
                if (!array_key_exists('id', $diff)) {
                    throw new Exception("'id' must be set as archived field");
                }

                $ids[] = $diff['id'];
            }

            if (empty($ids)) {
                return;
            }

            $conn = $this->entityManager->getConnection();
            $conn->beginTransaction();

            $idChunks = array_chunk($ids, 2000);
            foreach ($idChunks as $chunk) {
                $query = sprintf(
                    'DELETE FROM %s WHERE id IN (%s)',
                    $tableName,
                    implode(', ', $chunk)
                );
                $query = $this->removeSpecialChars($query);
                $conn->executeQuery($query);
                $conn->commit();
                $conn->beginTransaction();
            }

            if ($conn->isTransactionActive()) {
                $conn->commit();
            }
        }
    }
}
