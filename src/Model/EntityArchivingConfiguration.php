<?php

namespace Fastbolt\EntityArchiverBundle\Model;

class EntityArchivingConfiguration
{
    const ARCHIVING_STRATEGY_ARCHIVE = 'archive';
    const ARCHIVING_STRATEGY_REMOVE  = 'remove';

    private string $classname = '';

    private string $strategy  = '';

    /**
     * @var string[] $archivedFields
     */
    private array $archivedFields = [];

    private array $filters = [];

    private array $columnNames = [];

    private string $archiveTableSuffix = '';

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function setClassname(string $classname): EntityArchivingConfiguration
    {
        $this->classname = $classname;

        return $this;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function setStrategy(string $strategy): EntityArchivingConfiguration
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getArchivedFields(): array
    {
        return $this->archivedFields;
    }

    public function setArchivedFields(array $archivedFields): EntityArchivingConfiguration
    {
        $this->archivedFields = $archivedFields;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): EntityArchivingConfiguration
    {
        $this->filters = $filters;

        return $this;
    }

    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    public function setColumnNames(array $columnNames): EntityArchivingConfiguration
    {
        $this->columnNames = $columnNames;

        return $this;
    }

    public function getArchiveTableSuffix(): string
    {
        return $this->archiveTableSuffix;
    }

    public function setArchiveTableSuffix(string $archiveTableSuffix): EntityArchivingConfiguration
    {
        $this->archiveTableSuffix = $archiveTableSuffix;
        return $this;
    }
}
