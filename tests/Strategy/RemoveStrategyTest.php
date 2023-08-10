<?php

namespace App\Tests\Strategy;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy;
use Fastbolt\EntityArchiverBundle\Strategy\RemoveStrategy;
use PHPUnit\Framework\TestCase;

class RemoveStrategyTest extends TestCase
{
    public function testExecute(): void
    {
        $query = 'DELETE FROM users WHERE id IN (100, 101)';

        $metaData = $this->createMock(ClassMetadata::class);
        $metaData->method('getTableName')->willReturn('users');

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('executeQuery')->with($query);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metaData);
        $entityManager->method('getConnection')->willReturn($connection);

        $strategy = new RemoveStrategy($entityManager);

        $changes = [
            [
                'id' => 100,
                'username' => 'foo'
            ],
            [
                'id' => 101,
                'username' => 'bah'
            ]
        ];
        $archivingChange = new ArchivingChange();
        $archivingChange
            ->setStrategy('remove')
            ->setTotalEntities(100)
            ->setClassname('App\Entity\User')
            ->setArchivedColumns(['id', 'username'])
            ->setChanges($changes);


        $strategy->execute([$archivingChange]);
    }
}
