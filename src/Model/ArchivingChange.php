<?php

namespace Fastbolt\EntityArchiverBundle\Model;

class ArchivingChange
{
    /**
     * @var string
     */
    private string $classname;

    /**
     * Entities of a class that will be archived by executing the archiver
     *
     * @var array
     */
    private array $changes;

    /**
     * Unarchived entities of a single class (pre-execution)
     *
     * @var int
     */
    private int $totalEntities;

    /**
     * What the archiver is supposed to do with an entity that was selected to be changed (e.g., archive/remove)
     *
     * @var string
     */
    private string $strategy;

    /**
     * Database names of the columns that are copied to the archive table
     * @var array
     */
    private array $archivedColumns;

    /**
     * Complete name of the archive table, original table name + archive suffix
     *
     * @var string
     */
    private string $archiveTableName;

    /**
     * @return string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    /**
     * @param string $classname
     *
     * @return ArchivingChange
     */
    public function setClassname(string $classname): ArchivingChange
    {
        $this->classname = $classname;

        return $this;
    }

    /**
     * @return array
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * @param array $changes
     *
     * @return ArchivingChange
     */
    public function setChanges(array $changes): ArchivingChange
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalEntities(): int
    {
        return $this->totalEntities;
    }

    /**
     * @param int $totalEntities
     *
     * @return ArchivingChange
     */
    public function setTotalEntities(int $totalEntities): ArchivingChange
    {
        $this->totalEntities = $totalEntities;

        return $this;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function setStrategy(string $strategy): ArchivingChange
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getArchivedColumns(): array
    {
        return $this->archivedColumns;
    }

    public function setArchivedColumns(array $archivedColumns): ArchivingChange
    {
        $this->archivedColumns = $archivedColumns;
        return $this;
    }

    public function getArchiveTableName(): string
    {
        return $this->archiveTableName;
    }

    public function setArchiveTableName(string $archiveTableName): ArchivingChange
    {
        $this->archiveTableName = $archiveTableName;
        return $this;
    }
}
