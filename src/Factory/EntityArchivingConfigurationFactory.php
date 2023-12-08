<?php

namespace Fastbolt\EntityArchiverBundle\Factory;

use Fastbolt\EntityArchiverBundle\DependencyInjection\Configuration;
use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;

class EntityArchivingConfigurationFactory
{
    /**
     * @param array $configuration
     * @param array $strategies
     *
     * @return EntityArchivingConfiguration[]
     */
    public function create(array $configuration, array $strategies): array
    {
        $tableSuffix = $configuration['table_suffix'];
        $configurations = $configuration['entities'];
        $entityConfigurations = [];
        foreach ($configurations as $index => $configForEntity) {
            $fields = [];

            if (!array_key_exists('entity', $configForEntity)) {
                throw new \OutOfRangeException(
                    'Value for key \'entity\' not given for archiving configuration with index ' . $index
                );
            }
            $classname = $configForEntity['entity'];

            if (array_key_exists('fields', $configForEntity)) {
                $fields = $configForEntity['fields'];
            }

            $filters = [];
            if (array_key_exists('filters', $configForEntity)) {
                $filters = $configForEntity['filters'];
            }

            if (!array_key_exists($configForEntity['strategy'], $strategies)) {
                throw new \OutOfRangeException("Strategy not found: " . $configForEntity['strategy']);
            }
            $strategy = $strategies[$configForEntity['strategy']];

            $entityConfiguration = new EntityArchivingConfiguration();
            $entityConfiguration
                ->setStrategy($strategy)
                ->setClassname($classname)
                ->setArchivedFields($fields)
                ->setFilters($filters)
                ->setArchiveTableSuffix($tableSuffix)
                ->setAddArchivedAtField($configForEntity['addArchivedAt'])
                ->setArchivingDateFieldName(
                    $configForEntity['archivingDateFieldName']
                    ?? Configuration::ARCHIVING_DATE_FIELD_NAME_DEFAULT
                )
            ;

            $entityConfigurations[] = $entityConfiguration;
        }

        return $entityConfigurations;
    }
}
