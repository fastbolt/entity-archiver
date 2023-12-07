<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Doctrine\DBAL\Exception;
use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;
use Fastbolt\EntityArchiverBundle\Services\DeleteService;

class RemoveStrategy implements EntityArchivingStrategy
{
    use QueryManipulatorTrait;

    private ?StrategyOptions $options;

    private DeleteService $deleteService;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'remove';
    }

    /**
     * @return StrategyOptions
     */
    public function getOptions(): StrategyOptions
    {
        return $this->options;
    }

    /**
     * @param DeleteService $deleteService
     */
    public function __construct(
        DeleteService $deleteService
    ) {
        $this->deleteService = $deleteService;
        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(true)
            ->setCreatesArchiveTable(false);
    }

    /**
     * @param Transaction[] $changes
     *
     * @return void
     * @throws Exception
     */
    public function execute(array $transactions): void
    {
        $this->deleteService->deleteFromOriginTable($transactions);
    }
}
