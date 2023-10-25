<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;

class RemoveStrategy implements EntityArchivingStrategy
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    private ?StrategyOptions $options;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'remove';
    }

    public function getOptions(): StrategyOptions
    {
        return $this->options;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(true)
            ->setCreatesArchiveTable(false);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     * @throws Exception
     */
    public function execute(array $changes): void
    {
        $this->deleteFromOriginTable($changes);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     */
    protected function deleteFromOriginTable(array $changes): void
    {
        foreach ($changes as $change) {
            $metaData = $this->entityManager->getClassMetadata($change->getClassname());
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
