<?php

namespace Fastbolt\EntityArchiverBundle\Strategy;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Model\StrategyOptions;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;
use Fastbolt\EntityArchiverBundle\Services\DeleteService;

class RemoveStrategy implements EntityArchivingStrategy
{
    use QueryManipulatorTrait;

    private EntityManagerInterface $entityManager;

    private ?StrategyOptions $options;

    private DeleteService $deleteService;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'remove';
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
        DeleteService $deleteService
    )
    {
        $this->entityManager = $entityManager;
        $this->deleteService = $deleteService;
        $this->options = new StrategyOptions();
        $this->options
            ->setNeedsItemIdOnly(true)
            ->setCreatesArchiveTable(false);
    }

    /**
     * @param ArchivingChange[] $changes
     * @return void
     * @throws Exception
     */
    public function execute(array $changes): void
    {
        $this->deleteService->deleteFromOriginTable($changes);
    }
}
