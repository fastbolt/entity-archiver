<?php

namespace Fastbolt\EntityArchiverBundle\Services;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class DeleteService
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArchivingChange[] $changes
     *
     * @return void
     */
    public function deleteFromOriginTable(array $changes): void
    {
        foreach ($changes as $change) {
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

            $query = sprintf(
                'DELETE FROM %s WHERE id IN (%s)',
                $tableName,
                implode(', ', $ids)
            );

            $query = $this->removeSpecialChars($query);

            $this->entityManager->getConnection()->executeQuery($query);
        }
    }
}