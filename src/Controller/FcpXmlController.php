<?php

namespace App\Controller;

use App\Entity\Clip;
use App\Entity\Marker;
use App\Entity\Timeline;
use App\Entity\TimelineAsset;
use App\Entity\TimelineFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FcpXmlController extends AbstractController
{
    /**
     * @Route("/fcp/xml", name="fcp_xml")
     */
    public function index(Request $request)
    {
        // read the files in public/xml
        $finder = new Finder();
        $root = __DIR__ . '/../../public/xml'; // hack
        $finder->files()->in($root );

        foreach ($finder as $file) {
            // dumps the absolute path
            var_dump($file->getRealPath());

            // dumps the relative path to the file, omitting the filename
            var_dump($file->getRelativePath());

            // dumps the relative path to the file
            var_dump($file->getRelativePathname());
        }

        return $this->render('fcp_xml/index.html.twig', [
            'finder' => $finder,
            'root' => $root,
            'controller_name' => 'FcpXmlController',
        ]);
    }


    /**
     * @Route("/fcp/show_xml", name="fcp_xml_show")
     */
    public function showXml(Request $request)
    {

        // convert to Transcribe Timeline

        $timeline = new Timeline();
        $fn = $request->get('fn');
        $rawXml = file_get_contents($fn);
        $xml = simplexml_load_file($fn);
        $timeline->setFromXml($xml);





        // dump($timeline->getTimelineFormats()); die();
        // die();


        return $this->render('fcp_xml/show.html.twig', [
            'timeline' => $timeline,
            'rawXml' => $rawXml,
            'xml' => $xml,
            'fn' => $fn
        ]);

    }



    }
