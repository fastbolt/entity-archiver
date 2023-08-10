<?php

namespace App\Tests\Factory;

use PHPUnit\Framework\TestCase;

class EntityArchivingConfigurationFactoryTest extends TestCase
{
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
                'App\Entity\User' => [
                    'strategy' => 'remove',
                    'fields' => ['id', 'foo'],
                    'filters' => $filters
                ]
            ]
        ];

        $factory = new EntityArchivingConfigurationFactory();
        $result = $factory->create($configuration)[0];

        self::assertSame(
            'foo',
            $result->getArchiveTableSuffix(),
            'Archive table suffix not set correctly'
        );
        self::assertSame('remove', $result->getStrategy(), 'Strategy not set correctly');
        self::assertSame(['id', 'foo'], $result->getArchivedFields(), 'Archived fields not set correctly');
        self::assertSame($filters, $result->getFilters(), 'filters not set correctly');
    }

    public function testCreateWhenNoFieldsAndNoFilter(): void
    {
        $configuration = [
            'table_suffix' => 'foo',
            'entities' => [
                'App\Entity\User' => [
                    'strategy' => 'remove',
                ]
            ]
        ];

        $factory = new EntityArchivingConfigurationFactory();
        $result = $factory->create($configuration)[0];

        self::assertSame([], $result->getArchivedFields(), 'Archived fields should have not been set');
        self::assertEmpty($result->getFilters(), 'filters should have not been set');
    }
}
