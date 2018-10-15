<?php

namespace App\Command;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use wapmorgan\MediaFile\MediaFile;

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
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to media',
                "C:\\Users\\tacma\\OneDrive\\Pictures\\JUFJ\\Claire")
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    private function info($filename)
    {
        try {

            $media = MediaFile::open($filename);
            // for audio
            if ($media->isAudio()) {
                $audio = $media->getAudio();
                echo 'Duration: '.$audio->getLength().PHP_EOL;
                echo 'Bit rate: '.$audio->getBitRate().PHP_EOL;
                echo 'Sample rate: '.$audio->getSampleRate().PHP_EOL;
                echo 'Channels: '.$audio->getChannels().PHP_EOL;
            }
            // for video
            else {
                $video = $media->getVideo();
                // calls to VideoAdapter interface
                echo 'Duration: '.$video->getLength().PHP_EOL;
                echo 'Dimensions: '.$video->getWidth().'x'.$video->getHeight().PHP_EOL;
                echo 'Framerate: '.$video->getFramerate().PHP_EOL;
            }
        } catch (wapmorgan\MediaFile\Exceptions\FileAccessException $e) {
            // FileAccessException throws when file is not a detected media
        } catch (wapmorgan\MediaFile\Exceptions\ParsingException $e) {
            echo 'File is propably corrupted: '.$e->getMessage().PHP_EOL;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $io->note("Reading from $path");

        // Fetch the storage object
        /*
        $storage = new StorageClient();
        $bucketName = 'JUFJ';
        $objectName = 'amanda-.MOV.flac';
        $object = $storage->bucket($bucketName)->object($objectName);
        */


        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['mov', 'mp4'])) # meed a better isMovie function
            {
                continue; // skip
            }


            $filename = $file->getRelativePathname();

            // $this->info($file->getRealPath());

            if (!$media = $this->mediaRepository->findOneBy(['filename' => $filename]))
            {
                $media = (new Media())
                    ->setPath($file->getRealPath())
                    ->setFileSize($file->getSize())
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
