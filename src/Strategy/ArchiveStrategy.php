<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        InsertInArchiveService $insertService,
        DeleteService $deleteService
    ) {
        $this->insertService = $insertService;
        $this->deleteService = $deleteService;
        parent::__construct($entityManager);

        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(false)
            ->setCreatesArchiveTable(true);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     */
    public function execute(array $changes): void
    {
        $this->insertService->insertInArchive($changes);
        $this->deleteService->deleteFromOriginTable($changes);
    }
}
