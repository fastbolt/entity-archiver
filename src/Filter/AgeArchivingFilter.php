<?php

namespace Fastbolt\EntityArchiverBundle\Filter;

use Doctrine\ORM\Persisters\Exception\UnrecognizedField;
use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;
use Fastbolt\EntityArchiverBundle\QueryManipulatorTrait;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class AgeArchivingFilter implements EntityArchivingFilterInterface
{
    use QueryManipulatorTrait;

    private string $name = 'age';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function apply(
        string &$query,
        EntityArchivingConfiguration $configuration
    ): void {
        $filterConfig = null;
        foreach ($configuration->getFilters() as $filter) {
            if ($filter['type'] === 'age') {
                $filterConfig = $filter;
                break;
            }
        }

        if ($filterConfig === null) {
            throw new InvalidConfigurationException(
                'Configuration for filter '
                . $this::class
                . ' not found for entity '
                . $configuration->getClassname()
            );
        }

        $condition = $this->getCondition($configuration, $filterConfig);

        $query .= $this->getConditionTemplate($query) . $condition;
    }

    /**
     * @param EntityArchivingConfiguration $configuration
     * @param array $filterConfig
     * @return string
     * @throws UnrecognizedField
     */
    private function getCondition(EntityArchivingConfiguration $configuration, array $filterConfig): string
    {
        if (!array_key_exists('field', $filterConfig)) {
            throw new ParameterNotFoundException('field');
        }

        if (!array_key_exists('age', $filterConfig)) {
            throw new ParameterNotFoundException('age');
        }

        if (!array_key_exists('unit', $filterConfig)) {
            throw new ParameterNotFoundException('unit');
        }

        $unit      = $filterConfig['unit'];
        $age       = $filterConfig['age'];
        $date      = $this->formatDate(new \DateTime("- $age $unit"));
        $fieldName = $filterConfig['field'];

        if (!in_array($fieldName, $configuration->getColumnNames())) {
            throw new UnrecognizedField(
                "Attribute " . $fieldName . " not found on " . $configuration->getClassname()
                . '. Found: ' . implode(', ', $configuration->getColumnNames())
            );
        }

        return sprintf('%s < "%s"', $fieldName, $date);
    }
}
