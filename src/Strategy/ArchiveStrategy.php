<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;

class ArchiveStrategy extends RemoveStrategy implements EntityArchivingStrategy
{
    private EntityManagerInterface $entityManager;

    private ?StrategyOptions $options;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'archive';
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($entityManager);

        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(false)
            ->setCreatesArchiveTable(true);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     */
    public function execute(array $changes): void
    {
        $this->insertInArchive($changes);
        $this->deleteFromOriginTable($changes);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     * @throws Exception
     */
    protected function insertInArchive(array $changes): void
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
