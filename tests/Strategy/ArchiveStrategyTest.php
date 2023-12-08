<?php

namespace Fastbolt\EntityArchiverBundle\Tests\Strategy;

use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Services\MoveBetweenTablesService;
use Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy;
use Fastbolt\EntityArchiverBundle\Strategy\RemoveStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArchiveStrategyTest extends TestCase
{
    private MoveBetweenTablesService $moveService;

    /**
     * @var ArchiveStrategy[]|MockObject[]
     */
    private array $strategies;

    protected function setUp(): void
    {
        $this->moveService = $this->createMock(MoveBetweenTablesService::class);

        $remove = $this->createMock(RemoveStrategy::class);
        $this->strategies = ['remove' => $remove];
    }

    public function testExecute(): void
    {
        $strategy = new ArchiveStrategy($this->moveService);

        $changes = [
            [
                'id' => 100,
                'username' => 'foo'
            ],
        ];
        $archivingChange = new Transaction();
        $archivingChange
            ->setStrategy($this->strategies['remove'])
            ->setTotalEntities(100)
            ->setClassname('App\Entity\User')
            ->setArchivedColumns(['id', 'username'])
            ->setChanges($changes)
            ->setArchiveTableName('users_archive');

        $this->moveService->expects(self::once())->method('moveToArchive');

        $strategy->execute([$archivingChange]);
    }
}
