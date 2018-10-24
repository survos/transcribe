<?php

namespace App\Command;

use Done\Subtitles\Subtitles;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\MediaRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;


class ExportSrtCommand extends Command
{
    protected static $defaultName = 'app:export-srt';

    private $mediaRepository;
    private $projectRepository;
    private $em;

    public function __construct($name = null, EntityManagerInterface $em,
                                MediaRepository $mediaRepository, ProjectRepository $projectRepository)
    {
        parent::__construct($name);
        $this->mediaRepository = $mediaRepository;
        $this->projectRepository = $projectRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Export .srt files for use with Python, etc.')
            ->addArgument('projectCode', InputArgument::OPTIONAL, 'Project Code')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('option1')) {
            // ...
        }

        foreach ($this->mediaRepository->findAll() as $media) {
            $markers = $media->getMarkers();
            if ($markers->count()) {
                $subtitles = new Subtitles();
                foreach ($markers as $marker)
                {
                    $subtitles->add($marker->getStartTime() / 10, $marker->getEndTime() / 10, $marker->getNote());
                }
                $srt = $subtitles->content('srt');
                $srtFilename = $media->getPath() . '.srt'; // hmmm.
                if (!file_exists($srtFilename)) {
                    file_put_contents($srtFilename, $srt);
                    $io->success("$srtFilename written");
                }
            }
        }

        $io->success('Finished exporting .srt');
    }
}
