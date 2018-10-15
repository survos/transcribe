<?php

namespace App\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{

    private $em;
    private $mediaRepo;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->mediaRepo = $em->getRepository(Media::class);
    }

    private function getStorageObject(Media $media): ?StorageObject
    {
        // $filename = $media->getFilename();
        $flacFilename = $media->getAudioFileName();

        // see if we already have it on gs
        // Fetch the storage object
        $storage = new StorageClient([
            'keyFile' => (array)json_decode(getenv('GOOGLE_APPLICATION_CREDENTIALS'), true)
        ]);
        $bucketName = 'jufj';
        $objectName = basename($flacFilename);
        $object = $storage->bucket($bucketName)->object($objectName);
        return $object;

    }

    /**
     * @Route("/stream/{id}/{start}-{duration}.{_format}", name="media_stream")
     */
    public function stream_audio(Request $request, Media $media, $start=0, $duration = 10, $_format='mp3')
    {
        // path to raw audio (flac)
        $audioPath = sys_get_temp_dir() . '/' . $media->getAudioFileName();

        if ($_format == 'flac') {
            if (!file_exists($audioPath)) {
                $object = $this->getStorageObject($media);

                if ($object->exists()) {
                    $object->downloadToFile($audioPath);
                }
                $response = new BinaryFileResponse($audioPath, 200, ['Content-Type' => 'audio/mpeg3'], true, ResponseHeaderBag::DISPOSITION_INLINE);
                $response->headers->set('Content-Type', 'audio/mpeg3');
                return $response;
            }
        }



        // ffmpeg  -t 10 -ss 2 -i C:\Users\tacma\OneDrive\Pictures\JUFJ\Amanda\amanda--6.MOV.wav  x.wav

        $fn = sprintf("%d-%d-%d.mp3", $media->getId(), $start, $duration);

        if (!file_exists($fn)) {
            // if it exists locally, use it, otherwise open it on gs
            // argh, need to cache this somewhere!
            if (file_exists($audioPath)) {
                // $content = file_get_contents($audioPath);
            } else {
                $object = $this->getStorageObject($media);

                if ($object->exists()) {
                    $object->downloadToFile($audioPath);
                    // file_put_contents($audioPath, $content);
                } else {
                    throw new NotFoundException("$objectName on $bucketName does not exist " . $object->gcsUri());
                }


            }

            $ffmpeg = FFMpeg\FFMpeg::create();
            $audio = $ffmpeg->open($audioPath);
            $audio->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds($start), FFMpeg\Coordinate\TimeCode::fromSeconds($duration));

            $format = new FFMpeg\Format\Audio\Mp3();
            $audio->save($format, $fn);
        }



        $response =  new BinaryFileResponse($fn, 200, ['Content-Type' => 'audio/mpeg3'], true, ResponseHeaderBag::DISPOSITION_INLINE);
        $response->headers->set('Content-Type', 'audio/mpeg3');
        return $response;

        $content = file_get_contents($fn); // hack

        return new Response($content, 200, ['Content-Type' => 'audio/wav']);
        return $this->render('media/show.html.twig', [
            'media' => $media
        ]);
    }

    /**
     * @Route("/", name="media")
     */
    public function index()
    {
        return $this->render('media/index.html.twig', [
            'media' => $this->mediaRepo->findAll(),
            'controller_name' => 'MediaController',
        ]);
    }

    /**
     * @Route("/show/{id}", name="media_show")
     */
    public function show(Request $request, Media $media)
    {
        $object = $this->getStorageObject($media);
        return $this->render('media/show.html.twig', [
            'media' => $media,
            'object' => $object
        ]);
    }

}
