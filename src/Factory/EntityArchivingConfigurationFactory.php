<?php

namespace Fastbolt\EntityArchiverBundle\Factory;


use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;

class EntityArchivingConfigurationFactory
{
    /**
     * @param array $configurations
     *
     * @return EntityArchivingConfiguration[]
     */
    public function create(array $configuration): array
    {
        $tableSuffix = $configuration['table_suffix'];
        $configurations = $configuration['entities'];
        $entityConfigurations = [];
        foreach ($configurations as $classname => $configForEntity) {
            $fields = [];
            if (array_key_exists('fields', $configForEntity)) {
                $fields = $configForEntity['fields'];
            }

            $filters = [];
            if (array_key_exists('filters', $configForEntity)) {
                $filters = $configForEntity['filters'];
            }

            $entityConfiguration = new EntityArchivingConfiguration();
            $entityConfiguration
                ->setStrategy($configForEntity['strategy'])
                ->setClassname($classname)
                ->setArchivedFields($fields)
                ->setFilters($filters)
                ->setArchiveTableSuffix($tableSuffix);

            $entityConfigurations[] = $entityConfiguration;
        }

        return $entityConfigurations;
    }
}
