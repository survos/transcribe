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

    private $io;

    public function __construct(EntityManagerInterface $em,
                                MediaRepository $mediaRepository, ProjectRepository $projectRepository, $name = null)
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
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Skip saving')
            ->addOption('delete-photos', null, InputOption::VALUE_NONE, 'Delete existing photos')
        ;
    }

    private function streams($filename): ?FFMpeg\FFProbe\DataMapping\StreamCollection
    {

        $ffprobe = FFMpeg\FFProbe::create();
        try {
            $streams = $ffprobe
                ->streams($filename)// extracts file informations
                // ->all()
            ;
        } catch (\Exception $e) {
            $this->io->warning("Can't probe " . $filename);
            $streams = null;
        }

        return $streams;
    }

        private function info($filename)
    {

        $ffprobe = FFMpeg\FFProbe::create();
        /*
        $streams = $ffprobe
            ->streams($filename)// extracts file informations
            ->all()
        ;
        */

        try {

            $fileInfo = $ffprobe
                ->format($filename)// extracts file informations
                ->all();
        } catch (\Exception $e) {
            $this->io->error("Can't get fileInfo for $filename");
            return [];
        }

        /*
        if (strstr($filename, 'helly')) {
            dump($fileInfo);
            // $this->morganFileInfo($filename);
        }
        */
        return $fileInfo;

    }

    private function morganFileInfo($filename)
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
        $this->io = $io;

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

        if ($input->getOption('delete-photos')) {
            foreach ($project->getPhotos() as $photo) {
                $project->removeMedium($photo);
            }
            $this->em->flush();
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
        $finder->files()->in($dir); // ->contains('Shelly');

        foreach ($finder as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['mov', 'mp4', 'jpg'])) # meed a better isMovie function
            {
                continue; // skip
            }

            $filename = $file->getRelativePathname();
            $isImage = in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'gif', 'png'] );

            if (!$media = $this->mediaRepository->findOneBy(['filename' => $filename]))
            {
                $media = (new Media())
                    ->setPath($file->getRelativePath())
                    ->setFilename($filename);


                if ($isImage) {
                    $code =  'photo_' . pathinfo($media->getPath(), PATHINFO_FILENAME);
                    $media
                        ->setCode($code); // hack!
                } else {
                    $code = $media->getCode();
                }

                // if the code exists, throw an error
                if ($existingMedia = $this->mediaRepository->findOneBy([
                    'project'=> $project,
                    'code' => $media->getCode()]))
                {
                    // continue;
                    throw new \Exception($media->getCode() . '/' . $media->getFilename() . ' is already used by '. $existingMedia->getFilename());
                }
                $io->note(sprintf('Create %s: %s', $code, $file->getRealPath()) );

                $this->em->persist($media);
            }

            $info = $this->info($file->getRealPath());

            // $isImage = $info['format_name'] == 'image2';


            // @todo: get the properties from the internal array.
            if (empty($media->getStreamsJson() ))
            {
                $streamData = [];
                if ($streams = $this->streams($file->getRealPath())) {
                    if ($videos = $streams->videos())
                    {
                        $video = $videos->first();

                        $rFrameRate = $video->get('r_frame_rate');
                        if (list($x, $y) = explode('/', $rFrameRate)) {
                            $media
                                ->setFrameDuration(sprintf("%s/%s", $y, $x));
                        }
                        $media
                            ->setVideoStream($video->all())
                            ->setHeight($video->get('height'))
                            ->setWidth($video->get('width'))
                            ;
                    }

                }

                if ($streams) {
                    foreach ($streams as $stream) {
                        $streamData[] = $stream->all();
                    }
                    dump($streamData);
                    $media
                        ->setStreamsJson(json_encode($streamData));
                }
            }

            $media
                ->setType($isImage ? 'photo' : 'video')
                ->setStreamCount($info['nb_streams'])
                ->setFileSize($file->getSize())
                ->setProject($project)
                ->setDuration(round($info['duration']))
                ;

            $jsonFilename = $file->getRealPath() . '.json';
            if (file_exists($oldJson = $file->getRealPath() . 'json')) {
                rename($oldJson, $jsonFilename);
            }

            // really the JSON belongs on gs, with the flac, and arguably the video
            if (file_exists($jsonFilename)) {
                $media->setTranscriptRequested(true);
            }

            if ($isImage) {
                $media->setTranscriptRequested(true);
            }

            if (!$input->getOption('dry-run'))
            {
                $this->em->flush();
                $io->success(sprintf('File %s imported', $file->getRealPath()) );
            }

        }

        // recursively get all the files in path

        if (!$input->getOption('dry-run'))
        {
            $this->em->flush();
            $io->success('Files imported');
        } else {
            $io->success('Files read but not imported');
        }

    }
}
