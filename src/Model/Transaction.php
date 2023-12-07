<?php

namespace Fastbolt\EntityArchiverBundle\Model;

use Doctrine\ORM\Mapping\ClassMetadata;
use Fastbolt\EntityArchiverBundle\Strategy\EntityArchivingStrategy;

class Transaction
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
    private EntityArchivingStrategy $strategy;

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
     * Name of the table the data is read from
     *
     * @var string
     */
    private string $originalTableName;

    /**
     * @var ClassMetadata
     */
    private ClassMetadata $classMetaData;

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
     * @return Transaction
     */
    public function setClassname(string $classname): Transaction
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
     * @return Transaction
     */
    public function setChanges(array $changes): Transaction
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
     * @return Transaction
     */
    public function setTotalEntities(int $totalEntities): Transaction
    {
        $this->totalEntities = $totalEntities;

        return $this;
    }

    /**
     * @return EntityArchivingStrategy
     */
    public function getStrategy(): EntityArchivingStrategy
    {
        return $this->strategy;
    }

    /**
     * @param EntityArchivingStrategy $strategy
     *
     * @return $this
     */
    public function setStrategy(EntityArchivingStrategy $strategy): Transaction
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return array
     */
    public function getArchivedColumns(): array
    {
        return $this->archivedColumns;
    }

    /**
     * @param array $archivedColumns
     *
     * @return $this
     */
    public function setArchivedColumns(array $archivedColumns): Transaction
    {
        $this->archivedColumns = $archivedColumns;
        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveTableName(): string
    {
        return $this->archiveTableName;
    }

    /**
     * @param string $archiveTableName
     *
     * @return $this
     */
    public function setArchiveTableName(string $archiveTableName): Transaction
    {
        $this->archiveTableName = $archiveTableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalTableName(): string
    {
        return $this->originalTableName;
    }

    /**
     * @param string $originalTableName
     *
     * @return $this
     */
    public function setOriginalTableName(string $originalTableName): Transaction
    {
        $this->originalTableName = $originalTableName;

        return $this;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetaData(): ClassMetadata
    {
        return $this->classMetaData;
    }

    /**
     * @param ClassMetadata $classMetaData
     *
     * @return $this
     */
    public function setClassMetaData(ClassMetadata $classMetaData): Transaction
    {
        $this->classMetaData = $classMetaData;

        return $this;
    }
}
