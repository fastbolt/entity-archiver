<?php

namespace Fastbolt\EntityArchiverBundle;

use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Fastbolt\EntityArchiverBundle\Factory\EntityArchivingConfigurationFactory;
use Fastbolt\EntityArchiverBundle\Filter\EntityArchivingFilterInterface;
use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Fastbolt\EntityArchiverBundle\Model\EntityArchivingConfiguration;
use Fastbolt\EntityArchiverBundle\Strategy\EntityArchivingStrategy;
use InvalidArgumentException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Output\ConsoleOutput;

class ArchiveManager
{
    use QueryManipulatorTrait;

    /**
     * @var bool
     */
    private bool $isDryRun        = false;

    /**
     * @var bool
     */
    private bool $isUpdateSchemas = false;

    /**
     * @var EntityArchivingFilterInterface[]
     */
    private array $filters = [];

    /**
     * @var EntityArchivingStrategy[]
     */
    private array $strategies = [];

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityArchivingConfigurationFactory
     */
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

    /**
     * @param bool $isDryRun
     * @param bool $updateSchemas
     * @return $this
     */
    public function setOptions(bool $isDryRun = false, bool $updateSchemas = false): self
    {
        $this->isDryRun        = $isDryRun;
        $this->isUpdateSchemas = $updateSchemas;

        return $this;
    }

    /**
     * @return Transaction[]
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

        $entityConfigurations = $this->configurationFactory->create($configuration, $this->strategies);

        $transactions = [];
        foreach ($entityConfigurations as $entityConfig) {
            $metaData  = $this->entityManager->getClassMetadata($entityConfig->getClassname());
            $entityConfig->setColumnNames($metaData->getColumnNames());

            if ($this->isUpdateSchemas && $entityConfig->getStrategy()->getOptions()->isCreatesArchiveTable()) {
                $this->updateTableSchema($entityConfig);
            }

            // get changing table data
            $transactions[$entityConfig->getClassName()] = $this->getTransaction($entityConfig);
        }

        if (empty($transactions)) {
            return [];
        }

        if (!$this->isDryRun) {
            $this->applyTransactions($transactions);
        }

        return $transactions;
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

        $tableDraft       = new Table($archiveTableName);
        $fieldNames       = $metaData->getFieldNames();
        $archivedAtExists = false;
        foreach ($fieldNames as $fieldName) {
            if (!in_array($fieldName, $configuration->getArchivedFields())) {
                continue;
            }

            $columnName = $metaData->getColumnName($fieldName);
            $columnType = $metaData->getTypeOfField($fieldName);
            $tableDraft
                ->addColumn($columnName, $columnType)
                ->setNotnull(false)
                ->setDefault(null); //TODO this is not set for some reason

            if ($columnName === 'archived_at') {
                $archivedAtExists = true;
            }
        }

        if (empty($configuration->getArchivedFields())) {
            //if no fields given, archive all (foreign keys excluded)
            $configuration->setArchivedFields($configuration->getColumnNames());
        }

        if (!$archivedAtExists && $configuration->isAddArchivedAtField()) {
            $tableDraft->addColumn('archived_at', 'date');
        }

        if ($schemaManager->tablesExist($archiveTableName)) {
            $this->updateTableColumns($tableDraft);

            return;
        }

        $schemaManager->createTable($tableDraft);
    }

    /**
     * @param Table $tableDraft
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    private function updateTableColumns(Table $tableDraft): void
    {
        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();
        $comparator = $schemaManager->createComparator();

        $table = new Table($tableDraft->getName());
        foreach ($schemaManager->listTableColumns($tableDraft->getName()) as $column) {
            $table->addColumn($column->getName(), $column->getType()->getName());
        }

        $diff = $comparator->compareTables($table, $tableDraft);
        $schemaManager->alterTable($diff);
    }

    /**
     * Returns an object containing all entries of a table that will be affected by the archive command
     *
     * @param EntityArchivingConfiguration $configuration
     *
     * @return Transaction
     * @throws Exception
     */
    private function getTransaction(EntityArchivingConfiguration $configuration): Transaction
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
        if ($configuration->getStrategy()->getOptions()->isNeedsItemIdOnly()) {
            $query = "SELECT id FROM " . $tableName;
        } else {
            $columnSelect = $this->removeSpecialChars(implode(', ', $configuration->getArchivedFields()));

            $query = sprintf(
                "SELECT %s FROM %s",
                $columnSelect,
                $tableName
            );
        }

        // "archived_at" field may not exist in the original table, so we add it here
        if ($configuration->isAddArchivedAtField()) {
            $configuration->setArchivedFields(array_merge($configuration->getArchivedFields(), ['archived_at']));
        }

        $this->applyFilters($configuration, $query);

        $result = $this->entityManager
            ->getConnection()
            ->executeQuery($query)
            ->fetchAllAssociative();

        if ($configuration->isAddArchivedAtField()) {
            $date = (new DateTime())->format('Y-m-d H:i:s');
            foreach ($result as &$item) {
                $item['archived_at'] = $date;
            }
        }

        $transaction = new Transaction();
        $transaction
            ->setTotalEntities($entryCount)
            ->setClassname($configuration->getClassname())
            ->setStrategy($configuration->getStrategy())
            ->setArchivedColumns($configuration->getArchivedFields())
            ->setChanges($result)
            ->setOriginalTableName($tableName)
            ->setArchiveTableName($tableName . $configuration->getArchiveTableSuffix())
            ->setClassMetaData($metaData);

        return $transaction;
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
     * @param Transaction[] $transactions
     */
    private function applyTransactions(array $transactions): void
    {
        $actions = [];
        foreach ($transactions as $transaction) {
            $actions[$transaction->getStrategy()->getName()][] = $transaction;
        }

        foreach ($actions as $strategyName => $transactions) {
            if (!array_key_exists($strategyName, $this->strategies)) {
                throw new InvalidConfigurationException(
                    'Strategy ' . $strategyName . ' was not found. Found strategies for '
                    . implode(', ', array_keys($this->strategies))
                );
            }

            $this->strategies[$strategyName]->execute($transactions);
        }
    }
}
