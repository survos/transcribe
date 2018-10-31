<?php

namespace App\Controller;

use App\Entity\Clip;
use App\Entity\Marker;
use App\Entity\Timeline;
use App\Entity\TimelineAsset;
use App\Entity\TimelineFormat;
use App\Repository\ProjectRepository;
use App\Service\TimelineHelper;
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
            $xml = simplexml_load_file($file->getRealPath());
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
     * @Route("/fcp/show_xml", name="fcp_xml_show")
     */
    public function showXml(Request $request, TimelineHelper $helper, ProjectRepository $projectRepository)
    {

        // convert to Transcribe Timeline

        $timeline = new Timeline();
        if ($fn = $request->get('fn'))
        {
            $rawXml = file_get_contents($fn);
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
        $rawXml = tidy_repair_string($rawXml, ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0, 'hide-comments' => false]);

        // use a new timeline for the import!
        $importedTimeline = (new Timeline())->setFromXml($xml);





        // dump($timeline->getTimelineFormats()); die();
        // die();


        return $this->render('fcp_xml/show.html.twig', [
            'timeline' => $importedTimeline,
            'rawXml' => $rawXml,
            'xml' => $xml,
            'fn' => $fn,
            'link' => $link
        ]);

    }



    }
