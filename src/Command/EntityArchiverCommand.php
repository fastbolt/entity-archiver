<?php

namespace Fastbolt\EntityArchiverBundle\Command;

use Fastbolt\EntityArchiverBundle\ArchiveManager;
use Fastbolt\EntityArchiverBundle\Model\Transaction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntityArchiverCommand extends Command
{
    /**
     * @var ArchiveManager
     */
    private ArchiveManager $archiveManager;

    /**
     * @var array
     */
    private array $config;

    /**
     * @param ArchiveManager $archiveManager
     * @param array          $config
     */
    public function __construct(
        ArchiveManager $archiveManager,
        array $config
    ) {
        $this->archiveManager = $archiveManager;
        $this->config = $config;

        parent::__construct('entity-archiver:run');
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('entity-archiver:run')
            ->setDescription('Moves items to an archive table or removes them, based on the configurations')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Displays the statistics, but will not change any data'
            )
            ->addOption(
                'update-schema',
                null,
                InputOption::VALUE_NONE,
                'Updates the archive-table schemas'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Starting cleanup process');
        $this->archiveManager->setOptions(
            $input->getOption('dry-run'),
            $input->getOption('update-schema')
        );

        $changes = $this->archiveManager->runArchivingProcess($this->config);

        if (!empty($changes)) {
            $this->displayChanges($io, $changes);
            $io->success('Done.');
        } else {
            $io->warning('No entities were selected to be archived.');
        }


        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle  $io
     * @param Transaction[] $changes
     *
     * @return void
     */
    private function displayChanges(SymfonyStyle $io, array $changes): void
    {
        $table = $io->createTable()->setHeaders(
            [
                'class',
                'Total entries',
                'selected',
                'action'
            ]
        );

        foreach ($changes as $change) {
            $table->addRow(
                [
                    $change->getClassname(),
                    $change->getTotalEntities(),
                    count($change->getChanges()),
                    $change->getStrategy()->getName()
                ]
            );
        }

        $alignRight = (new TableStyle())->setPadType(STR_PAD_LEFT);

        $table
            ->setColumnStyle(1, $alignRight)
            ->setColumnStyle(2, $alignRight);

        $table->render();
    }
}
