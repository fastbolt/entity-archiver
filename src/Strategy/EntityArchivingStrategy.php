<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;

interface EntityArchivingStrategy
{
    /**
     * Returns the name of the strategy as written in the entity-archiver.yaml
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Executes the strategy on all passed entries
     *
     * @param ArchivingChange[] $changes
     * @return void
     */
    public function execute(array $changes): void;

    /**
     * True if only the id of the item is needed for the process, e.g. when removing an item
     *
     * @return StrategyOptions
     */
    public function getOptions(): StrategyOptions;
}
