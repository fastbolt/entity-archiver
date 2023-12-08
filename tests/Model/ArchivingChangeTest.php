<?php

namespace Fastbolt\EntityArchiverBundle\Tests\Model;

use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Strategy\RemoveStrategy;
use PHPUnit\Framework\TestCase;

class ArchivingChangeTest extends TestCase
{
    public function testGetterSetter(): void
    {
        $strategy = $this->createMock(RemoveStrategy::class);
        $change = new Transaction();
        $change
            ->setArchivedColumns(['columns'])
            ->setClassname('foo')
            ->setStrategy($strategy)
            ->setTotalEntities(1000)
            ->setArchiveTableName('ham')
            ->setChanges(['eggs']);

        $this->assertSame(['columns'], $change->getArchivedColumns(), 'archivedColumns was not set correctly');
        $this->assertSame('foo', $change->getClassname(), 'classname was not set correctly');
        $this->assertSame($strategy, $change->getStrategy(), 'strategy was not set correctly');
        $this->assertSame('ham', $change->getArchiveTableName(), 'tableName was not set correctly');
        $this->assertSame(['eggs'], $change->getChanges(), 'attribute "changes" was not set correctly');
    }
}
