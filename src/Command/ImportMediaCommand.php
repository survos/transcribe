<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Repository\MediaRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use wapmorgan\MediaFile\MediaFile;

use FFMpeg;


class ImportMediaCommand extends Command
{
    protected static $defaultName = 'app:import-media';

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
            ->addArgument('projectCode', InputArgument::REQUIRED, 'Project Code')
            ->addOption('dir', null, InputOption::VALUE_OPTIONAL, 'root dir for project')
            ->addOption('skip-info', null, InputOption::VALUE_NONE, 'Skip ffprobe')
        ;
    }

    private function info($filename)
    {

        $ffprobe = FFMpeg\FFProbe::create();
        $fileInfo = $ffprobe
            ->format($filename) // extracts file informations
            ->all();

        return $fileInfo;

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

        $projectCode = $input->getArgument('projectCode');
        if (!$project = $this->projectRepository->findOneBy(['code' => $projectCode]))
        {
            $project = (new Project())
                ->setCode($projectCode);
            $this->em->persist($project);

            // needs a directory if it's new
            if (!$dir = $input->getOption('dir'))
            {
                $helper = $this->getHelper('question');
                $question = new Question('Please enter the path: ', '/home/tac/Videos');
                $dir = $helper->ask($input, $output, $question);
            }
        }

        // update the dir in the project, may have changed with --dir
        if (!empty($dir) || $dir = $input->getOption('dir'))
        {
            $project
                ->setBasePath($dir);
        }

        // now that we have a project, read the files

        $dir = $project->getBasePath();
        $io->note("Reading from $dir");

        // Fetch the storage object
        /*
        $storage = new StorageClient();
        $bucketName = 'JUFJ';
        $objectName = 'amanda-.MOV.flac';
        $object = $storage->bucket($bucketName)->object($objectName);
        */


        $finder = new Finder();
        $finder->files()->in($dir);

        foreach ($finder as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['mov', 'mp4'])) # meed a better isMovie function
            {
                continue; // skip
            }


            $filename = $file->getRelativePathname();

            $info = $this->info($file->getRealPath());

            if (!$media = $this->mediaRepository->findOneBy(['filename' => $filename]))
            {
                $media = (new Media())
                    ->setPath($file->getRealPath())
                    ->setFilename($filename);
                $this->em->persist($media);
            }
            print $file->getRealPath() . " " . $file->getSize()  . "\n";
            $media
                ->setProject($project)
                ->setDuration(round($info['duration']))
                ->setFileSize($media->calcFileSize())
                ;

            if (file_exists($file->getRealPath() . 'json')) {
                $media->setTranscriptRequested(true);
            }

        }

        // recursively get all the files in path

        $this->em->flush();

        $io->success('Files imported');
    }
}
