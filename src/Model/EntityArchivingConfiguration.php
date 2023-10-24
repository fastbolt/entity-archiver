<?php

namespace Fastbolt\EntityArchiverBundle\Model;

class EntityArchivingConfiguration
{
    public const ARCHIVING_STRATEGY_ARCHIVE = 'archive';
    public const ARCHIVING_STRATEGY_REMOVE  = 'remove';

    private string $classname = '';

    private string $strategy  = '';

    /**
     * @var string[] $archivedFields
     */
    private array $archivedFields = [];

    private array $filters = [];

    private array $columnNames = [];

    private string $archiveTableSuffix = '';

    private bool $addArchivedAtField = true;

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
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param string $strategy
     *
     * @return $this
     */
    public function setStrategy(string $strategy): EntityArchivingConfiguration
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

    public function isAddArchivedAtField(): bool
    {
        return $this->addArchivedAtField;
    }

    public function setAddArchivedAtField(bool $addArchivedAtField): EntityArchivingConfiguration
    {
        $this->addArchivedAtField = $addArchivedAtField;

        return $this;
    }
}
