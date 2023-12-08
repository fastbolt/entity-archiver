<?php

namespace Fastbolt\EntityArchiverBundle\Model;

use Fastbolt\EntityArchiverBundle\Strategy\EntityArchivingStrategy;

class EntityArchivingConfiguration
{
    public const ARCHIVING_STRATEGY_ARCHIVE = 'archive';
    public const ARCHIVING_STRATEGY_REMOVE  = 'remove';

    private string $classname = '';

    private ?EntityArchivingStrategy $strategy  = null;

    /**
     * @var string[] $archivedFields
     */
    private array $archivedFields = [];

    private array $filters = [];

    private array $columnNames = [];

    private string $archiveTableSuffix = '';

    private bool $addArchivedAtField = false;

    private string $archivingDateFieldName = 'archived_at';

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
     * @return $this
     */
    public function setClassname(string $classname): EntityArchivingConfiguration
    {
        $this->classname = $classname;

        return $this;
    }

    /**
     * @return EntityArchivingStrategy|null
     */
    public function getStrategy(): ?EntityArchivingStrategy
    {
        return $this->strategy;
    }

    /**
     * @param EntityArchivingStrategy|null $strategy
     *
     * @return $this
     */
    public function setStrategy(?EntityArchivingStrategy $strategy): EntityArchivingConfiguration
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getArchivedFields(): array
    {
        return $this->archivedFields;
    }

    /**
     * @param array $archivedFields
     *
     * @return $this
     */
    public function setArchivedFields(array $archivedFields): EntityArchivingConfiguration
    {
        $this->archivedFields = $archivedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters): EntityArchivingConfiguration
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * @param array $columnNames
     *
     * @return $this
     */
    public function setColumnNames(array $columnNames): EntityArchivingConfiguration
    {
        $this->columnNames = $columnNames;

        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveTableSuffix(): string
    {
        return $this->archiveTableSuffix;
    }

    /**
     * @param string $archiveTableSuffix
     *
     * @return $this
     */
    public function setArchiveTableSuffix(string $archiveTableSuffix): EntityArchivingConfiguration
    {
        $this->archiveTableSuffix = $archiveTableSuffix;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAddArchivedAtField(): bool
    {
        return $this->addArchivedAtField;
    }

    /**
     * @param bool $addArchivedAtField
     *
     * @return $this
     */
    public function setAddArchivedAtField(bool $addArchivedAtField): EntityArchivingConfiguration
    {
        $this->addArchivedAtField = $addArchivedAtField;

        return $this;
    }

    public function getArchivingDateFieldName(): string
    {
        return $this->archivingDateFieldName;
    }

    public function setArchivingDateFieldName(string $archivingDateFieldName): EntityArchivingConfiguration
    {
        $this->archivingDateFieldName = $archivingDateFieldName;

        return $this;
    }
}
