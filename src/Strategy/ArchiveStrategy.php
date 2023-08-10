<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;

class ArchiveStrategy extends RemoveStrategy implements EntityArchivingStrategy
{
    private EntityManagerInterface $entityManager;

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
            if (!array_key_exists('archivedAt', $change)) { //TODO is key name correct?
                $change['archived_at'] = $this->formatDate(new DateTime());
            }
        }
        $entityChange->setChanges($changes);
    }
}
