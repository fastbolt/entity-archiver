<?php

namespace Fastbolt\EntityArchiverBundle\Model;

class StrategyOptions
{
    private bool $needsItemIdOnly = false;

    private bool $createsArchiveTable = true;

    public function isNeedsItemIdOnly(): bool
    {
        return $this->needsItemIdOnly;
    }

    public function setNeedsItemIdOnly(bool $needsItemIdOnly): StrategyOptions
    {
        $this->needsItemIdOnly = $needsItemIdOnly;

        return $this;
    }

    public function isCreatesArchiveTable(): bool
    {
        return $this->createsArchiveTable;
    }

    public function setCreatesArchiveTable(bool $createsArchiveTable): StrategyOptions
    {
        $this->createsArchiveTable = $createsArchiveTable;

        return $this;
    }
}