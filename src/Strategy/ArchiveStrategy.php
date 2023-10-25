<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use DateTime;
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
            ->setNeedsItemIdOnly(true)
            ->setCreatesArchiveTable(false);
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
            $this->addArchivedAtField($entityChange);

            $columnNames = $entityChange->getArchivedColumns();
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

    /**
     * @return void
     */
    protected function addArchivedAtField(ArchivingChange $entityChange): void
    {
        $columns = $entityChange->getArchivedColumns();
        if (!array_key_exists('archivedAt', $columns)) {
            $columns['archivedAt'] = 'archived_at';
            $entityChange->setArchivedColumns($columns);
        }

        $changes = $entityChange->getChanges();
        foreach ($changes as &$change) {
            if (!array_key_exists('archivedAt', $change)) {
                $change['archived_at'] = $this->formatDate(new DateTime());
            }
        }
        $entityChange->setChanges($changes);
    }
}
