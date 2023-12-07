<?php

namespace Fastbolt\EntityArchiverBundle\Model;

class StrategyOptions
{
    private bool $needsItemIdOnly = false;

    private bool $createsArchiveTable = true;

    /**
     * @return bool
     */
    public function isNeedsItemIdOnly(): bool
    {
        return $this->needsItemIdOnly;
    }

    /**
     * @param bool $needsItemIdOnly
     *
     * @return $this
     */
    public function setNeedsItemIdOnly(bool $needsItemIdOnly): StrategyOptions
    {
        $this->needsItemIdOnly = $needsItemIdOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreatesArchiveTable(): bool
    {
        return $this->createsArchiveTable;
    }

    /**
     * @param bool $createsArchiveTable
     *
     * @return $this
     */
    public function setCreatesArchiveTable(bool $createsArchiveTable): StrategyOptions
    {
        $this->createsArchiveTable = $createsArchiveTable;

        return $this;
    }
}
