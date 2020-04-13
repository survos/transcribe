<?php

namespace App\Controller;

use App\Entity\Mlt;
use App\Entity\Producer;
use App\Entity\Profile;
use App\Entity\Property;
use App\Services\MltService;
use Soothsilver\DtdParser\Dtd\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

// DTD: https://raw.githubusercontent.com/mltframework/mlt/master/src/modules/xml/mlt-xml.dtd

class KdenController extends AbstractController
{

    const MARC_PATTERN_DEFINITION_ANYELEMENT = '/<!ELEMENT/';
    const MARC_PATTERN_DEFINITION_EMPTYELEMENT = '/<!ELEMENT (.*) EMPTY>/i';
    const MARC_PATTERN_DEFINITION_ENTITY = '/%(.*)\;/i';

    private function Get_ElementList($File="")
    {
        /*
         * reads file;
         * replaces unwanted "spaces"
         */
        $File = file_get_contents($File);
        $File = preg_replace("/[[:cntrl:]]+/", "", $File);

        /*
         * searches for elements;
         * searches for entities
         */
        $Result = preg_match_all(self::MARC_PATTERN_DEFINITION_ELEMENT_NAMECONTENT, $File, $Elements, PREG_SET_ORDER);
        $Result = preg_match_all(self::MARC_PATTERN_DEFINITION_ENTITY_BLOCK, $File, $Entities, PREG_SET_ORDER);

        /*
         * converts entities to usable form
         */
        $this -> Convert_SimplifyEntities($Entities);
        $this -> Convert_PrepareEntities($Entities);

        /*
         * iterates list of elements and sets closing part of element and usable siblings
         */
        foreach ($Elements as $Element)
        {
            self::$List_AvailableElements[$Element['ElementName']]['ClosingPart'] = ($Element['ElementSetting'] == MarC::MARC_OPTION_EMPTY ? $Element['ElementName'] : '/'.$Element['ElementName']);
            self::$List_AvailableElements[$Element['ElementName']]['Siblings'] = $this -> Get_Siblings($Element['ElementSetting'], $Entities);
        }
    }

    const DTD = 'https://raw.githubusercontent.com/mltframework/mlt/master/src/modules/xml/mlt-xml.dtd';
    /**
     * @Route("/mlt-dtd", name="kden_dtd")
     */
    public function dtd(MltService $mltService)
    {

        $newFiles = $mltService->generateClasses();

        $dtdText = file_get_contents(__DIR__ . '/../../mlt-xml.dtd');
        $dtd = \Soothsilver\DtdParser\DTD::parseText($dtdText);

        return $this->render('mlt/dtd.html.twig', [
            'elements' => $dtd->elements,
            'newFiles' => $newFiles
        ]);

        dd($dtd);

        $dtd = <<< END
<?xml version="1.0"?>
<!DOCTYPE note [
<!ELEMENT note (to,from,heading,body)>
<!ELEMENT to (#PCDATA)>
<!ELEMENT from (#PCDATA)>
<!ELEMENT heading (#PCDATA)>
<!ELEMENT body (#PCDATA)>
]>
<note>
<to>Tove</to>
<from>Jani</from>
<heading>Reminder</heading>
<body>Don't forget me this weekend</body>
</note>
END;

        $dom = new \DOMDocument();
        // $dom->load('book.xml');
        $dom->loadXML($dtd);
        $domType = $dom->doctype;
        /** @var \DOMEntity $childNode */
        foreach ($domType->childNodes as $childNode) {
            dump(get_class($childNode));
            try {
                var_dump($childNode->actualEncoding);
                // var_dump($childNode);
            } catch (\Exception $exception) {
                dump($exception->getMessage());
            }
            dump($childNode->textContent);
            dump($childNode->getLineNo());
            dd($domType);
        }
        dd($domType);
        if ($dom->validate()) {
            echo "This document is valid!\n";
        } else {
            dd('Invalid!');
        }


        $crawler = new Crawler($dtd);
        $xml = simplexml_load_string($dtd);
        dd($xml, $dtd);

    }


    // see https://github.com/mltframework/mlt/blob/master/src/modules/xml/mlt-xml.dtd

    private function getTestXml()
    {
        return <<< END
<?xml version='1.0' encoding='utf-8'?>
<mlt LC_NUMERIC="en_US.UTF-8" producer_ref="main_bin" version="7.11" root="/home/tac/Mlt">
 <profile frame_rate_num="30000" sample_aspect_num="1" display_aspect_den="1080" colorspace="601" progressive="1" description="1920x1080 29.97fps" display_aspect_num="1920" frame_rate_den="1001" width="1920" height="1080" sample_aspect_den="1"/>
 <producer id="producer0" in="00:00:00.000" out="00:02:17.117">
      <property id='10' name="length">4110</property>
  </producer>
</mlt>
END;
    }
    /**
     * @Route("/kden-writer", name="kden_writer")
     */
    public function writer(SerializerInterface $serializer)
    {
        $expected = '<mlt LC_NUMERIC="en_US.UTF-8" producer="main_bin" version="6.21.0" root="/home/tac/Videos">';
        $mlt = (new Mlt())
            ->setRoot('My Written Root')
            ->setLcNumeric('en_US.UTF-8')
            // ->setProducer('survos_bin')
            ->setVersion('6-writer')
            ;

        $expectedProfile = <<< END
 frame_rate_num="30000" sample_aspect_num="1" display_aspect_den="1080" colorspace="601" progressive="1" description="1920x1080 29.97fps" display_aspect_num="1920" frame_rate_den="1001" width="1920" height="1080" sample_aspect_den="1"/>
END;

        $profile = (new Profile())
            ->setFrameRateNum(30000)
            ->setSampleAspectNum(1)
            ;
        $mlt->addProfile($profile);

        $producer = (new Producer())
            ->setId(99)
            ->setInTime('just In Time')
            ->setOutTime('Time Out, dude!')
            ;
        $producer->addProperty((new Property())->setName('color')->setValue('blue'));
        $producer->addProperty((new Property())->setName('size')->setValue('large'));
        $mlt->addProducer($producer);

        $context = [
            'xml_root_node_name' => 'mlt',
            'groups' => ['xml'],
            'xml_format_output' => true,
        ];
        $xml = $serializer->serialize($mlt, 'xml', $context);

        dd($xml, $this->getTestXml(), $mlt, $profile);
        return new Response($xml, 200, ['Content-Type' => 'text/xml']);

        $xmlEncoder = new XmlEncoder();
        $xmlEncoder->setSerializer($serializer);
        $xml = $xmlEncoder->encode($mlt, 'xml', [ 'xml_format_output' => true, 'xml_root_node_name' => 'mlt', 'groups' => ['xml']]);

        // $xml = $serializer->serialize($mlt, 'xml', ['xml_root_node_name' => 'mlt']);
        return new Response($xml, 200, ['Content-Type' => 'text/xml']);
        dd($xml);


    }

    /**
     * @Route("/kden", name="kden_reader")
     */
    public function reader(SerializerInterface $serializer)
    {

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        // $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $encoders = [new XmlEncoder()];
        $normalizers = [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)];
        $serializer = new Serializer($normalizers, $encoders);

        $xml = '<property id="20" name="length">4110</property>';
        $object = $serializer->deserialize($xml, Property::class, 'xml');
        dump($xml, $object);

        /*
        $xml = '<profile width="1080" />';
        $object = $serializer->deserialize($xml, Profile::class, 'xml');
        dump($xml, $object);
        */
        $xml = '
<producer id="1234" in="0:2:4">
    <property id="20" name="length">4110</property>
    <property id="21" name="length">22</property>
</producer>';

        $object = $serializer->deserialize($xml, Producer::class, 'xml');
        dump($xml, $object, $object->getProperties());

        $mlt = (new Mlt())->setVersion('1.0');

        $context = [
            'xml_root_node_name' => 'mlt',
            'groups' => ['xml'],
            'xml_format_output' => true,
        ];

        // $xml = $serializer->serialize($mlt, 'xml', $context);

        $context = [
            'xml_root_node_name' => 'property',
            'groups' => ['xml'],
            'xml_format_output' => true,
        ];

        $xml = $this->getTestXml();
        $object = $serializer->deserialize($xml, Mlt::class, 'xml');
        dump($xml, $object, $object->getProfiles());

        dd('stopped');
        // $xml = $this->getTestXml();
        // $xml = '<mlt version="1.1">data</mlt>';
        $data = $serializer->deserialize($xml, Mlt::class, 'xml');

        dd($data);

        $crawler = new Crawler($xml);

        $crawler = $crawler->filterXPath('//default:mlt/producer');

        foreach ($crawler as $domElement) {
            $simple =  simplexml_import_dom($domElement);

            dump($domElement->nodeName, $simple->attributes());

            foreach ($domElement->childNodes as $childNode) {
                dump($childNode);
                if ($childNode->hasAttributes()) {
                    $simple =  simplexml_import_dom($childNode);
                    dump($simple->attributes());
                }

            }
        }

        dd($crawler);

        return $this->render('kden/index.html.twig', [
            'controller_name' => 'KdenController',
        ]);
    }


}
