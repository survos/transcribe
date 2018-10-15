<?php

namespace App\Command;

use App\Entity\Media;
use Google\ApiCore\OperationResponse;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
    private $em;
    /** @var SymfonyStyle */
    private $io;

    public function __construct($name=null, EntityManagerInterface $em, MediaRepository $mediaRepository)
    {
        parent::__construct($name);
        $this->mediaRepository = $mediaRepository;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Transcribe videos using Google Speech API')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('force', null, InputOption::VALUE_NONE, 're-do transcription')
            ->addOption('upload', null, InputOption::VALUE_NONE, 'upload to gs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->io = $io;

        $qb = $this->mediaRepository->createQueryBuilder('m')
            ->where('m.transcriptRequested = true');

        if (!$input->getOption('force')) {
            $qb
                ->andWhere('m.transcriptJson IS NULL');
        }
            ;
        /** @var Media $media */

        // @todo Note: Speech-to-Text supports WAV files with LINEAR16 or MULAW encoded audio.  So we could store wav data in db and stream it.
        foreach ($qb->getQuery()->getResult() as $media) {

            $filename = $media->getPath();
            $flacFilename = $media->getAudioFilePath();

            // see if we already have it on gs
            // Fetch the storage object
            $storage = new StorageClient();
            $bucketName = 'jufj';
            $objectName = basename($flacFilename);
            $object = $storage->bucket($bucketName)->object($objectName);

            if (!$object->exists()) {
                $this->io->error($objectName . ' does not exist');
                if ( !file_exists($flacFilename)) {
                    $io->note("Creating flac for $filename");
                    // $command = "ffmpeg -i $filename -c:a wav  -ac 1 $flacFilename";
                    $command = "ffmpeg -i $filename -ac 1 $flacFilename";
                    $io->note($command);
                    exec($command);
                }

                if ($input->getOption('upload'))
                {
                    $file = fopen($flacFilename, 'r');
                    $bucket = $storage->bucket($bucketName);
                    $object = $bucket->upload($file, [
                        'name' => $objectName
                    ]);
                }
            }



            // $io->note(sprintf("Flac file $flacFilename is %d bytes", ($data)) );

            $io->note("Transcribing $flacFilename");
            if ($jsonResult = $this->transcribe_auto_punctuation($object))
            {
                $cacheFile = $filename . 'json';
                file_put_contents($cacheFile, $jsonResult);
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



            $media
                ->setTranscriptJson($jsonResult);
            $this->em->flush();
            print $media->getTranscriptJson();

        }


        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $io->success('Finished transcribing');
    }

    /**
     * Transcribe the given audio file with auto punctuation enabled
     */
    function transcribe_auto_punctuation(StorageObject $object)
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
        $client = new SpeechClient();


        /** @var OperationResponse $operationResponse */
        $operationResponse = $client->longRunningRecognize($config, $audio);

        $operationResponse->pollUntilComplete();

             if ($operationResponse->operationSucceeded()) {

                 /** @var LongRunningRecognizeResponse $results */
                 $results = $operationResponse->getResult();
                 printf("Class: %s\n", get_class($results));
                 // dump($results);
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
            printf("Class: %s\n", get_class($result));
            dump($result);
            $alternatives = $result->getAlternatives();
            if (!empty($alternatives[0]))
            {
                $x[] = json_decode($result->serializeToJsonString());
                $mostLikely = $alternatives[0];

                $transcript = $mostLikely->getTranscript();
                $confidence = $mostLikely->getConfidence();
                /** @var WordInfo $wordInfo */
                foreach ($mostLikely->getWords() as $wordInfo) {
                    $words[] = [
                        // $wordInfo->serializeToJsonString()
                    ];
                }
                printf('Transcript: %s' . PHP_EOL, $transcript);
                printf('Confidence: %s' . PHP_EOL, $confidence);

            }
        }

        return  json_encode($x, JSON_PRETTY_PRINT);
    }

}
