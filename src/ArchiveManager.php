<?php

namespace Fastbolt\EntityArchiverBundle;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Factory\EntityArchivingConfigurationFactory;
use Fastbolt\EntityArchiverBundle\Filter\EntityArchivingFilterInterface;
use Fastbolt\EntityArchiverBundle\Model\ArchivingChange;
use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;
use Fastbolt\EntityArchiverBundle\Strategy\EntityArchivingStrategy;
use InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Output\ConsoleOutput;

class ArchiveManager
{
    use QueryManipulatorTrait;

    private bool $isDryRun        = false;

    private bool $isUpdateSchemas = false;

    /**
     * @var EntityArchivingFilterInterface[]
     */
    private array $filters = [];

    /**
     * @var EntityArchivingStrategy[]
     */
    private array $strategies = [];

    private EntityManagerInterface $entityManager;

    private EntityArchivingConfigurationFactory $configurationFactory;

    /**
     * @param EntityManagerInterface              $entityManager
     * @param EntityArchivingConfigurationFactory $configurationFactory
     * @param EntityArchivingFilterInterface[]    $filters
     *
     * @throws \Exception
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityArchivingConfigurationFactory $configurationFactory,
        iterable $filters,
        iterable $strategies
    ) {
        $this->entityManager        = $entityManager;
        $this->configurationFactory = $configurationFactory;

        foreach ($filters as $filter) {
            $this->filters[$filter->getName()] = $filter;
        }

        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getName()] = $strategy;
        }

        if (empty($this->filters)) {
            $output = new ConsoleOutput();
            $output->getErrorOutput()->writeln('Filters were not autowired, manually adding filters now');
        }

        if (empty($this->strategies)) {
            $output = new ConsoleOutput();
            $output->getErrorOutput()->writeln('Strategies were not autowired, manually adding strategies now');
        }
    }

    public function setOptions(bool $isDryRun = false, bool $updateSchemas = false): self
    {
        $this->isDryRun        = $isDryRun;
        $this->isUpdateSchemas = $updateSchemas;

        return $this;
    }

    /**
     * @return ArchivingChange[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function runArchivingProcess(array $configuration): array
    {
        if (!array_key_exists('table_suffix', $configuration)) {
            throw new \Exception(
                "Parameter 'table_suffix' is not defined in entity_archiver.yaml and
                 no default value could be retrieved"
            );
        }

        $entityConfigurations = $this->configurationFactory->create($configuration);

        $changes = [];
        foreach ($entityConfigurations as $entityConfig) {
            if ($this->isUpdateSchemas) {
                $this->updateTableSchema($entityConfig);
            }

            // get changing table data
            $changes[$entityConfig->getClassName()] = $this->getChange($entityConfig);
        }

        if (empty($changes)) {
            return [];
        }

        foreach ($changes as $change) {
            if (empty($change->getChanges())) {
                return [];
            }
        }

        if (!$this->isDryRun) {
            $this->applyChanges($changes);
        }

        return $changes;
    }

    /**
     * @param EntityArchivingConfiguration $configuration
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    private function updateTableSchema(EntityArchivingConfiguration $configuration): void
    {
        $metaData         = $this->entityManager->getClassMetadata($configuration->getClassname());
        $tableName        = $metaData->getTableName();
        $archiveTableName = $tableName . $configuration->getArchiveTableSuffix();
        $schemaManager    = $this->entityManager->getConnection()->createSchemaManager();
        if ($schemaManager->tablesExist($archiveTableName)) {
            //TODO check if schema changes are needed
            return;
        }

        $table      = new Table($archiveTableName);
        $fieldNames = $metaData->getFieldNames();
        $archivedAtExists = false;
        foreach ($fieldNames as $fieldName) {
            $columnName = $metaData->getColumnName($fieldName);
            $columnType = $metaData->getTypeOfField($fieldName);
            $table
                ->addColumn($columnName, $columnType)
                ->setNotnull(false)
                ->setDefault(null); //TODO this is not set for some reason

            if ($columnName === 'archived_at') {
                $archivedAtExists = true;
            }
        }

        if (!$archivedAtExists) {
            $table->addColumn('archived_at', 'date');
        }

        $schemaManager->createTable($table);
    }

    /**
     * Returns an object containing all entries of a table that will be affected by the archive command
     *
     * @param EntityArchivingConfiguration $configuration
     *
     * @return ArchivingChange
     * @throws Exception
     */
    private function getChange(EntityArchivingConfiguration $configuration): ArchivingChange
    {
        $metaData  = $this->entityManager->getClassMetadata($configuration->getClassname());
        $tableName = $metaData->getTableName();

        //get total entry count
        $tableName = $this->removeSpecialChars($tableName);
        $countQuery = sprintf("SELECT COUNT(*) FROM %s", $tableName);
        $entryCount = $this->entityManager
            ->getConnection()
            ->executeQuery($countQuery)
            ->fetchOne();

        //get entries that will be archived
        $columnNames = $this->getColumnNames($configuration);
        $configuration->setColumnNames($columnNames);

        $columnSelect = $this->removeSpecialChars(implode(', ', $columnNames));
        $query = sprintf(
            "SELECT %s FROM %s",
            $columnSelect,
            $tableName
        );

        $this->applyFilters($configuration, $query);

        $result = $this->entityManager
            ->getConnection()
            ->executeQuery($query)
            ->fetchAllAssociative();

        $change = new ArchivingChange();
        $change
            ->setTotalEntities($entryCount)
            ->setClassname($configuration->getClassname())
            ->setStrategy($configuration->getStrategy())
            ->setArchivedColumns($configuration->getColumnNames())
            ->setChanges($result)
            ->setArchiveTableName($tableName . $configuration->getArchiveTableSuffix());

        return $change;
    }

    /**
     * @param EntityArchivingConfiguration $configuration
     * @param string $query
     * @return void
     */
    private function applyFilters(EntityArchivingConfiguration $configuration, string &$query): void
    {
        $filters = $configuration->getFilters();

        foreach ($filters as $filter) {
            $filterType = $filter['type'];
            if (!array_key_exists($filterType, $this->filters)) {
                throw new InvalidArgumentException(
                    "No filter with name '$filterType' found. Found: " . implode(', ', array_keys($this->filters))
                );
            }

            $this->filters[$filterType]->apply($query, $configuration);
        }
    }

    /**
     * @param ArchivingChange[] $changes
     */
    private function applyChanges(array $changes): void
    {
        $actions = [];
        foreach ($changes as $change) {
            $actions[$change->getStrategy()][] = $change;
        }

        foreach ($actions as $strategyName => $changes) {
            if (!array_key_exists($strategyName, $this->strategies)) {
                throw new InvalidConfigurationException(
                    'Strategy ' . $strategyName . ' was not found. Found strategies for '
                    . implode(', ', array_keys($this->strategies))
                );
            }

            $this->strategies[$strategyName]->execute($changes);
        }
    }

    private function getColumnNames(EntityArchivingConfiguration $configuration): array
    {
        $metaData = $this->entityManager->getClassMetadata($configuration->getClassname());

        if (empty($configuration->getArchivedFields())) {
            $fields = [];
            foreach ($metaData->getColumnNames() as $colName) {
                $fields[$colName] = $metaData->getFieldName($colName);
            }

            $configuration->setArchivedFields($fields);
        }

        $columnNames = [];
        foreach ($configuration->getArchivedFields() as $field) {
            $columnNames[$field] = $metaData->getColumnName($field);
        }

        return $columnNames;
    }
}
