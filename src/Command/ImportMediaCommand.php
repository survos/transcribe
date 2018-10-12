<?php

namespace App\Command;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ImportMediaCommand extends Command
{
    protected static $defaultName = 'app:import-media';

    private $mediaRepository;
    private $em;

    public function __construct($name = null, EntityManagerInterface $em, MediaRepository $mediaRepository)
    {
        parent::__construct($name);
        $this->mediaRepository = $mediaRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to media')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $io->note("Reading from $path");

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['mov', 'mp4'])) # meed a better isMovie function
            {
                continue; // skip
            }

            $filename = $file->getRelativePathname();
            if (!$media = $this->mediaRepository->findOneBy(['filename' => $filename]))
            {
                $media = (new Media())
                    ->setPath($file->getRealPath())
                    ->setFilename($filename);
                $this->em->persist($media);
            }
        }

        // recursively get all the files in path


        if ($input->getOption('option1')) {
            // ...
        }

        $this->em->flush();

        $io->success('Files imported');
    }
}
