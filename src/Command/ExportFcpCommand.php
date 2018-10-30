<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\Timeline;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportFcpCommand extends Command
{
    protected static $defaultName = 'app:export-fcp';

    private $em;
    private $projectRepo;

    public function __construct(EntityManagerInterface $entityManager, ?string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->projectRepo = $entityManager->getRepository(Project::class);
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max Seconds', 180)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $projectCode = $input->getArgument('projectCode');

        $project = $this->projectRepo->findOneBy(['code' => $projectCode]);



        $xml = $this->createXml($project, (new Timeline())->setMaxDuration($input->getOption('max')));

        $fn = '../' . $project->getCode() . '-import.fcpxml';
        file_put_contents($fn, $xml);


        $io->success(sprintf('%s exported.', $fn));
    }
}
