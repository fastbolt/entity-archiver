<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;
use Fastbolt\EntityArchiverBundle\Services\MoveBetweenTablesService;

class ArchiveStrategy implements EntityArchivingStrategy
{
    private ?StrategyOptions $options;

    private MoveBetweenTablesService $moveService;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'archive';
    }

    public function getOptions(): StrategyOptions
    {
        return $this->options;
    }

    public function __construct(
        MoveBetweenTablesService $moveService
    ) {

        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(false)
            ->setCreatesArchiveTable(true);

        $this->moveService = $moveService;
    }

    /**
     * @param Transaction[] $changes
     *
     * @return void
     */
    public function execute(array $changes): void
    {
        $this->moveService->moveToArchive($changes);
    }
}
