<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\Timeline;
use App\Service\TimelineHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class ExportFcpCommand extends Command
{
    protected static $defaultName = 'app:export-fcp';

    private $em;
    private $projectRepo;
    private $helper;
    private $twig;

    public function __construct(EntityManagerInterface $entityManager, TimelineHelper $helper, Environment $twig_Environment, ?string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->projectRepo = $entityManager->getRepository(Project::class);
        $this->helper = $helper;
        $this->twig = $twig_Environment;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('projectCode', InputArgument::OPTIONAL, 'Project')
            ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Max Seconds', 180)
            ->addOption('srt', null, InputOption::VALUE_NONE, 'SRT too')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $projectCode = $input->getArgument('projectCode');

        $project = $this->projectRepo->findOneBy(['code' => $projectCode]);

        $max = $input->getOption('max'); // should be in project somehow.


        $timeline = $this->helper->updateTimelineFromProject($project, (new Timeline())->setMaxDuration($input->getOption('max')));

        $xml = $this->twig->render('timeline_xml.twig', [
            'timeline' => $timeline
        ]);

        // file_put_contents('/tmp/' . $project->getCode() . '-import.fcpxml', $xml);
        // format the raw xml
        if (function_exists('tidy_repair_string')) {
            $xml = tidy_repair_string($xml, ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0, 'hide-comments' => false]);
        }
        $fn = 'C:\\JUFJ\\temp\\' .  $project->getCode() . '.fcpxml';
        file_put_contents($fn, $xml);
        $io->success(sprintf('%s exported.', $fn));

        if ($input->getOption('srt')) {
            $subtitles = $this->helper->getMarkerSubtitles($project, $max);

            $fn = 'C:\\JUFJ\\temp\\' .  $project->getCode() . '.srt';
            file_put_contents($fn,  $subtitles->content('srt'));
            $io->success(sprintf('%s exported.', $fn));
        }

    }
}
