<?php

namespace Fastbolt\EntityArchiverBundle\Tests\Strategy;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy;
use PHPUnit\Framework\TestCase;

class ArchiveStrategyTest extends TestCase
{
    public function testExecute(): void
    {
        $metaData = $this->createMock(ClassMetadata::class);
        $metaData->method('getTableName')->willReturn('users');

        $res = $this->createMock(Result::class);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::exactly(2))
            ->method('executeQuery')
            ->willReturnCallback(function ($query) use ($res) {
                if (str_contains($query, "INSERT")) {
                    self::assertStringContainsString('INSERT INTO users_archive (id, username, archived_at) VALUES ("100", "foo", "', $query);
                } else {
                    self::assertStringContainsString("DELETE FROM users WHERE id IN (100, 101)", $query);
                }

                return $res;
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturn($metaData);
        $entityManager->method('getConnection')->willReturn($connection);

        $strategy = new ArchiveStrategy($entityManager);

        $changes = [
            [
                'id' => 100,
                'username' => 'foo'
            ],
            [
                'id' => 101,
                'username' => 'bar'
            ]
        ];
        $archivingChange = new ArchivingChange();
        $archivingChange
            ->setStrategy('remove')
            ->setTotalEntities(100)
            ->setClassname('App\Entity\User')
            ->setArchivedColumns(['id', 'username'])
            ->setChanges($changes)
            ->setArchiveTableName('users_archive');

        $strategy->execute([$archivingChange]);
    }
}
