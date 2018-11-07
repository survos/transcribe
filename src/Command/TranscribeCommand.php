<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Word;
use App\Repository\ProjectRepository;
use App\Repository\WordRepository;
use Google\ApiCore\OperationResponse;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Proxies\__CG__\App\Entity\Marker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;

# use Google\Cloud\Speech\Result;

# [START speech_transcribe_auto_punctuation]
use Google\Cloud\Speech\V1\SpeechRecognitionResult;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\WordInfo;

use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\LongRunningRecognizeResponse;

class TranscribeCommand extends Command
{
    protected static $defaultName = 'app:transcribe';

    private $mediaRepository;
    private $projectRepository;
    private $wordRepository;
    private $em;
    /** @var SymfonyStyle */
    private $io;

    private $storageClient;

    public function __construct($name=null, EntityManagerInterface $em, MediaRepository $mediaRepository,
                                ProjectRepository $projectRepository,
    WordRepository $wordRepository
    )
    {
        parent::__construct($name);
        $this->mediaRepository = $mediaRepository;
        $this->projectRepository = $projectRepository;
        $this->wordRepository = $wordRepository;
        $this->em = $em;

        // probably a way to auto-wire this!
        $this->storageClient = new StorageClient([

        ]);
    }

    protected function configure()
    {
        $this
            ->setDescription('Transcribe videos using Google Speech API')
            ->addArgument('projectCode', InputArgument::REQUIRED, 'Project Code')
            ->addOption('force', null, InputOption::VALUE_NONE, 're-do transcription')
            ->addOption('upload-flac', null, InputOption::VALUE_NONE, 'upload flac to gs')
            ->addOption('upload-thumb', null, InputOption::VALUE_NONE, 'upload thumbnail to gs')
            ->addOption('upload-photos', null, InputOption::VALUE_NONE, 'upload photos to gs')
            ->addOption('mp3', null, InputOption::VALUE_NONE, 'upload mp3 to gs')
            ->addOption('transcribe', null, InputOption::VALUE_NONE, 'call text-to-speech')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;
        $projectCode = $input->getArgument('projectCode');
        if (!$project = $this->projectRepository->findOneBy(['code' => $projectCode]))
        {
            throw new \Exception("Project $projectCode not found.");
        }



        $qb = $this->mediaRepository->createQueryBuilder('m')
            ->andWhere('m.transcriptRequested = true')
            ->andWhere("m.type = 'video'")
        ;

        if (!$input->getOption('force')) {
            $qb
                ->andWhere('m.transcriptJson IS NULL');
        }

        if ($input->getOption('upload-photos')) {
            $this->uploadPhotos($project);
        }

        if ($input->getOption('upload-thumb')) {
            $this->uploadThumbnails($project);
        }

            ;
        /** @var Media $media */

        // Note: Speech-to-Text also supports WAV files with LINEAR16 or MULAW encoded audio.


        foreach ($qb->getQuery()->getResult() as $media) {

            $projectCode = $media->getProject()->getCode();
            $bucket = $this->getBucket($projectCode);

            $filename = $media->getPath();


            $flacFilename = $media->getAudioFilePath();
        $objectName = basename($flacFilename); // hmm, might need the directory here!
        $object = $bucket->object($objectName);

        if ($object->exists()) {
            $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
            $media->setFlacExists(true);
            $io->note(sprintf("Public Flac file exists in gs: %s ", $object->name()) );
        }

            $flacFilename = $media->getAudioFilePath();

            $objectName = basename($flacFilename); // hmm, might need the directory here!
            $object = $bucket->object($objectName);

            // if object is not in cloud
            if (!$object->exists()) {
                $this->io->note($objectName . ' does not exist');
                $this->createFlac($media->getRealPath('\\'), $flacFilename, $io);

                if ($input->getOption('upload-flac'))
                {
                    /*
                    $options = ['gs' => ['acl' => 'public-read']];
                    $context = stream_context_create($options);
                    $fileName = "gs://${my_bucket}/public_file.txt";
                    file_put_contents($fileName, $publicFileText, 0, $context);
                    */


                    // $publicUrl = CloudStorageTools::getPublicUrl($fileName, false);

                    $file = fopen($flacFilename, 'r');
                    $object = $bucket->upload($file, [
                        'name' => $objectName
                    ]);
                }
            }

            if ($object->exists()) {
                $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
                $media->setFlacExists(true);
                $io->note(sprintf("Public Flac file exists in gs: %s ", $object->name()) );
            }

            // $io->note(sprintf("Flac file $flacFilename is %d bytes", ($data)) );
            $cacheFile = $filename . '.json';
            if (file_exists($cacheFile)) {
                $io->note("Using cache file: $cacheFile");
                $jsonResult = file_get_contents($cacheFile);
            } else {
                $jsonResult = null;
                if ($input->getOption('transcribe')) {
                    $io->note("Transcribing $flacFilename");
                    if ($jsonResult = $this->transcribe_auto_punctuation($object, $media))
                    {
                        file_put_contents($cacheFile, $jsonResult);
                    }
                }
            }

            /*
            $data = file_get_contents($flacFilename);

            // get contents of a file into a string
            $handle = fopen($flacFilename, 'r');
            $content = fread($handle, filesize($flacFilename));
            fclose($handle);

            // Create the speech client
            $languageCode = 'en-US';
            $speech = new SpeechClient([
                'languageCode' => $languageCode,
            ]);

            // When true, time offsets for every word will be included in the response.
            $options['enableWordTimeOffsets'] = true;
            $options['enableWordTimeOffsets'] = true;

            // Make the API call
            $results = $speech->recognize(
                fopen($flacFilename, 'r'),
                $options
            );
            */


            if ($jsonResult && ($media->getWords()->count() == 0) ) {
                $io->note("Import words from JSON");

                $result = json_decode($jsonResult, true);
                if (empty($result)) {
                    continue;
                }
                // $media->getWords()->clear(); // if you've made edits or added markers, this is problematic.
                // $this->em->flush(); // hack

                $idx = 0;
                foreach ($result as $sentence) {
                    $x = $sentence['alternatives'][0];
                    foreach ($x['words'] as $word) {
                        $idx++;
                        try {
                            foreach (['startTime', 'endTime'] as $v) {
                                $word[$v] = sprintf("%3.1f", rtrim($word[$v], 's'));
                            }
                            $w = $word['word']; // the string
                            $lastChar = substr($w, -1);
                            if ( in_array($lastChar, ['?', '.', '!']) ) {
                                $punct = $lastChar;
                                // remove last char from word?
                            } else {
                                $punct = null;
                            }
                            $wordObject = (new Word())
                                // ->setMedia($media)
                                ->setIdx($idx)
                                ->setEndPunctuation($punct)
                                ->setWord($w)
                                ->setStartTime($word['startTime'])
                                ->setEndTime($word['endTime'])
                            ;
                        } catch (\Exception $e) {
                            dump($word);
                            throw new \Exception($e->getMessage());
                        }
                        $media->addWord($wordObject);
                    }
                }

                $media
                    ->setTranscriptRequested(true) // since it already exists
                    ->setTranscriptJson($jsonResult);

            }
            $this->em->flush();

        }

        $this->em->flush();


        $io->success('Finished transcribing');
    }

    public function uploadThumbnails(Project $project)
    {
        foreach ($this->mediaRepository->findBy([
            'type' => 'video',
            'project' => $project
        ]) as $media) {
            $bucket = $this->getBucket($project->getCode());

            // this is the name of the JPEG file.  At some point, we'll also export an image from the video
            $filename = $media->getPath();

            $objectName = basename($filename); // hmm, might need the directory here!
            $object = $bucket->object($objectName);
            // first, create and upload thumbnail
            $thumbFilename = $media->getThumbFilePath();

            $objectName = basename($thumbFilename); // hmm, might need the directory here!
            $object = $bucket->object($objectName);

            // if object is not in cloud
            if (!$object->exists()) {
                $this->io->note($objectName . ' does not exist');
                $this->createThumb($filename, $thumbFilename);

                $file = fopen($thumbFilename, 'r');
                $object = $bucket->upload($file, [
                    'name' => $objectName
                ]);
            }

            if ($object->exists()) {
                $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
                // $media->setFlacExists(true);
                $this->io->note(sprintf("Public Thumb file exists in gs: %s ", $object->name()));
            }
        }
    }

    public function uploadPhotos(Project $project)
    {
        foreach ($this->mediaRepository->findBy([
            'type' => 'photo',
            'project' => $project
        ]) as $media) {
            $bucket = $this->getBucket($project->getCode());

            // this is the name of the JPEG file.  At some point, we'll also export an image from the video
            $filename = $media->getRealPath();

            $objectName = ($media->getPath()); // hmm, might need the directory here!
            $object = $bucket->object($objectName);

            // if object is not in cloud
            if (!$object->exists())
            {
                $this->io->note($objectName . ' does not exist');

                    /*
                    $options = ['gs' => ['acl' => 'public-read']];
                    $context = stream_context_create($options);
                    $fileName = "gs://${my_bucket}/public_file.txt";
                    file_put_contents($fileName, $publicFileText, 0, $context);
                    */


                    // $publicUrl = CloudStorageTools::getPublicUrl($fileName, false);

                    $file = fopen($filename, 'r');
                    $object = $bucket->upload($file, [
                        'name' => $objectName
                    ]);
            }

            if ($object->exists()) {
                try {
                    $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
                    $media->setFlacExists(true);
                    $this->io->note(sprintf("Public file exists in gs: %s %s ",
                        $object->name(), $media->getPublicUrl('')) );
                } catch (\Exception $e) {
                    $this->io->error($e->getMessage());
                }
            }

        }
    }

    /**
     * Transcribe the given audio file with auto punctuation enabled
     */
    function transcribe_auto_punctuation(StorageObject $object, Media $media)
    {


        // get contents of a file into a string
        /* moved to Google Storage
        $handle = fopen($path, 'r');
        $content = fread($handle, filesize($path));
        fclose($handle);
        */


        $uri = $object->gcsUri();

        $this->io->note($uri);
        // set string as audio content
        $audio = (new RecognitionAudio())
            ->setUri($uri);

        // set config
        $config = (new RecognitionConfig())
            // ->setEncoding(AudioEncoding::LINEAR16)
            // ->setSampleRateHertz(32000)
            ->setLanguageCode('en-US')
            ->setEnableAutomaticPunctuation(true)
            ->setEnableWordTimeOffsets(true)
        ;

        // create the speech client
        $client = new SpeechClient([
            'keyFile' => 'x'
        ]);


        /** @var OperationResponse $operationResponse */
        $operationResponse = $client->longRunningRecognize($config, $audio);

        $operationResponse->pollUntilComplete();

             if ($operationResponse->operationSucceeded()) {

                 /** @var LongRunningRecognizeResponse $results */
                 $results = $operationResponse->getResult();
                 // doSomethingWith($result)
             } else {
                 $error = $operationResponse->getError();
                 // handleError($error)
             }
        // make the API call
        // $response = $client->recognize($config, $audio);
        //$results = $response->getResults();

        // print results
        $x = [];


        /** @var SpeechRecognitionResult $result */
        foreach ($results->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            if (!empty($alternatives[0]))
            {
                $x[] = json_decode($result->serializeToJsonString());
                $mostLikely = $alternatives[0];

                $transcript = $mostLikely->getTranscript();
                $confidence = $mostLikely->getConfidence();

                printf('Transcript: %s' . PHP_EOL, $transcript);
                printf('Confidence: %s' . PHP_EOL, $confidence);

            }
        }

        return  json_encode($x, JSON_PRETTY_PRINT);
    }

    /**
     * @param $filename
     * @param $flacFilename
     * @param $io
     */
    protected function createFlac($filename, $flacFilename, $io): void
    {
        if (!file_exists($flacFilename)) {
            $io->note("Creating flac for $filename");
            // $command = "ffmpeg -i $filename -c:a wav  -ac 1 $flacFilename";
            $command = "ffmpeg -i $filename -ac 1 $flacFilename";
            $this->io->note($command);
            exec($command);
        }
    }

    protected function createThumb($filename, $jpgFilename): void
    {
        if (!file_exists($jpgFilename)) {
            $this->io->note("Creating flac for $filename");
            // $command = "ffmpeg -i $filename -c:a wav  -ac 1 $flacFilename";
            $command = sprintf('ffmpeg -i "%s" -vframes 1  "%s"', $filename, $jpgFilename);
            $this->io->note($command);
            exec($command);
        }
    }

    /**
     * Create a Cloud Storage Bucket.
     *
     * @param string $bucketName name of the bucket to create.
     * @param string $options options for the new bucket.
     *
     * @return Google\Cloud\Storage\Bucket the newly created bucket.
     */
    function getBucket($bucketName, $options = [], $purgeFirst = false): Bucket
    {
        // delete it while testing
        $bucketName = 'survos_' . $bucketName;

        $bucket = $this->storageClient->bucket($bucketName);

        //
        if ($bucket->exists()) {
            if ($purgeFirst) {
                $bucket->delete();
            } else {
                return $bucket;
            }
        }

        $options = [
            'predefinedAcl' => 'publicRead',
            // 'storageClass' => 'REGIONAL',
            'acl' => 'public-read'
        ];

        // if (!$bucket = $this->storageClient->bucket($bucketName))
        {
            $bucket = $this->storageClient->createBucket($bucketName, $options);
        }

        return $bucket;
    }


}
