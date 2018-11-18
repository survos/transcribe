<?php

namespace App\Controller;

use App\Element\Fcpxml;
use App\Entity\Clip;
use App\Entity\Marker;
use App\Entity\Timeline;
use App\Entity\TimelineAsset;
use App\Entity\TimelineFormat;
use App\Repository\ProjectRepository;
use App\Service\TimelineHelper;
use FluidXml\FluidXml;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
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
        $root = "C:/JUFJ/temp"; // hack
        $root = $this->getParameter('xml_root');
        $finder->files()->in($root )->name('*.fcpxml');

        foreach ($finder as $file) {
            try {
                $xml = simplexml_load_file($file->getRealPath());
            } catch (\Exception $e) {
                // ignore it for now?
                continue;
            }
            $data[$file->getBasename()] =
                [
                    'version' => $xml['version'],
                    'file' => $file
                ];
        }

        return $this->render('fcp_xml/index.html.twig', [
            'finder' => $finder,
            'root' => $root,
            'data' => $data
        ]);
    }

    /**
     * @Route("/fcp/test_xml", name="fcp_test_xml")
     */
    public function testXml(Request $request, TimelineHelper $helper, ProjectRepository $projectRepository)
    {
        // also see
        // http://www.ethanjoachimeldridge.info/tech-blog/manipulating-XML-with-PHP
        if ($fn = $request->get('fn')) {
            $rawXml = file_get_contents($fn);

            $xmlDoc = new \DOMDocument();
            $xmlDoc->load($fn);

            print $xmlDoc->saveXML() . "\n";

            // $fcp = FluidXml::load($fn);
            dump($xmlDoc);
            die("stopped in controller");

            $fcp = $helper->importXml($rawXml);
        }



        $crawler = new Crawler($rawXml);

        foreach ($crawler as $domElement) {
            var_dump($domElement->nodeName);
        }

        die();

        // convert to Transcribe Timeline

        $timeline = new Timeline();

    }



            /**
     * @Route("/fcp/show_xml", name="fcp_xml_show")
     */
    public function showXml(Request $request, TimelineHelper $helper, ProjectRepository $projectRepository)
    {

        // convert to Transcribe Timeline

        $timeline = new Timeline();
        if ($fn = $request->get('fn'))
        {

            $fcpxml = new Fcpxml($fn);
            $formats = $fcpxml->getFormats();
            foreach ($formats as $format) {
                dump($format);
            }
            die();


            $xmlDoc = new \DOMDocument();
            $xmlDoc->load($fn);

            // https://developer.apple.com/library/archive/documentation/FinalCutProX/Reference/FinalCutProXXMLFormat/FCPXMLDTD/FCPXMLDTD.html
            $formats = $xmlDoc->getElementsByTagName('format');
            foreach ($formats as $format) {
                // dump($format);
            }

            foreach ($xmlDoc->getElementsByTagName('clip') as $clip) {
                dump($clip, $clip->saveXML());
            }

            die();
            $domXml = $xmlDoc->saveXML();

            $rawXml = file_get_contents($fn);

            // try it with the XML Serializer


            $xml = simplexml_load_file($fn);
            $link = $fn; // pass through?

        } elseif ($projectCode = $request->get('code'))
        {
            $project = $projectRepository->findOneBy(['code' => $projectCode]);
            $timeline = $helper->updateTimelineFromProject($project);
            $rawXml = $this->renderView('timeline_xml.twig', [
                'timeline' => $timeline
            ]);
            $xml = simplexml_load_string($rawXml);
            $link = $this->generateUrl('project_xml', $project->rp());
        }

        // format the raw xml
        if (function_exists('tidy_repair_string')) {
            $rawXml = tidy_repair_string($rawXml, ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0, 'hide-comments' => false]);
            // $domXml = tidy_repair_string($domXml, ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0, 'hide-comments' => false]);
        }

        // use a new timeline for the import!
        $timeline = new Timeline();
        $importedTimeline = $helper->updateTimelineFromXml($xml, $timeline);



        // dump($timeline->getTimelineFormats()); die();
        // die();

        return $this->render('fcp_xml/show.html.twig', [
            'timeline' => $importedTimeline,
            'rawXml' => $rawXml,
            'xml' => $xml,
            // 'domXml' => $domXml,
            'fn' => $fn,
            'link' => $link
        ]);

    }



    }
