<?php

namespace App\Command;

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

class FixMediaCommand extends Command
{

    protected static $defaultName = 'app:fix-media';
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
            ->setDescription('Add code to media')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->mediaRepository->findAll() as $media) {
            // if (empty($media->getCode()))
            {

                if ($media->getWidth() == 0) {
                    dump($media); // die();
                }

                /* fix code and type */
                $code =  pathinfo($media->getFilename(), PATHINFO_FILENAME);
                $media->setCode($code);

                $finfo = new \finfo();
                if (file_exists($media->getPath())) {
                    $finfo = $finfo->file($media->getPath());
                    if (strpos($finfo, 'movie') !== false) {
                        $type = 'video';
                    } else {
                        die($finfo);
                    }
                    $media->setType($type);
                }
            }
        }

        $this->em->flush();
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('Media updated');
    }
}
