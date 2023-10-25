<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;
use Fastbolt\EntityArchiverBundle\Services\DeleteService;
use Fastbolt\EntityArchiverBundle\Services\InsertInArchiveService;

class ArchiveStrategy implements EntityArchivingStrategy
{
    private ?StrategyOptions $options;

    private InsertInArchiveService $insertService;

    private DeleteService $deleteService;

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
        InsertInArchiveService $insertService,
        DeleteService $deleteService
    ) {
        $this->insertService = $insertService;
        $this->deleteService = $deleteService;

        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(false)
            ->setCreatesArchiveTable(true);
    }

    /**
     * @param Transaction[] $changes
     *
     * @return void
     */
    public function execute(array $transactions): void
    {
        $this->insertService->insertInArchive($transactions);
        $this->deleteService->deleteFromOriginTable($transactions);
    }
}
