<?php

namespace Fastbolt\EntityArchiverBundle\Tests\Factory;

use Fastbolt\EntityArchiverBundle\Factory\EntityArchivingConfigurationFactory;
use Fastbolt\EntityArchiverBundle\Strategy\ArchiveStrategy;
use Fastbolt\EntityArchiverBundle\Strategy\RemoveStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityArchivingConfigurationFactoryTest extends TestCase
{
    /**
     * @var ArchiveStrategy[]|MockObject[]
     */
    private array $strategies;

    public function setUp(): void
    {
        $remove = $this->createMock(RemoveStrategy::class);

        $this->strategies = ['remove' => $remove];
    }

    public function testCreate(): void
    {
        $filters = [
            [
                'type' => 'age',
                'age' => 10,
                'unit' => 'years',
                'field' => 'changedAt'
            ]
        ];

        $configuration = [
            'table_suffix' => 'foo',
            'entities' => [
                [
                    'entity'                 => 'App\Entity\User',
                    'addArchivedAt'          => true,
                    'archivingDateFieldName' => 'eggs',
                    'strategy'               => 'remove',
                    'fields'                 => ['id', 'foo'],
                    'filters'                => $filters,
                ]
            ]
        ];

        $factory = new EntityArchivingConfigurationFactory();
        $result        = $factory->create($configuration, $this->strategies)[0];

        self::assertSame(
            'foo',
            $result->getArchiveTableSuffix(),
            'Archive table suffix not set correctly'
        );
        self::assertSame($this->strategies['remove'], $result->getStrategy(), 'Strategy not set correctly');
        self::assertSame(['id', 'foo'], $result->getArchivedFields(), 'Archived fields not set correctly');
        self::assertSame($filters, $result->getFilters(), 'filters not set correctly');
    }

    public function testCreateWhenNoFieldsAndNoFilter(): void
    {
        $configuration = [
            'table_suffix' => 'foo',
            'entities' => [
                [
                    'addArchivedAt' => false,
                    'entity'        => 'App\Entity\User',
                    'strategy'      => 'remove',
                    'archivingDatre'
                ]
            ]
        ];

        $factory = new EntityArchivingConfigurationFactory();
        $result        = $factory->create($configuration, $this->strategies)[0];

        self::assertSame([], $result->getArchivedFields(), 'Archived fields should have not been set');
        self::assertEmpty($result->getFilters(), 'filters should have not been set');
    }
}
