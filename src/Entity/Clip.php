<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClipRepository")
 */
class Clip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=48)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=1, nullable=true)
     */
    private $track_offset;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimelineAsset", inversedBy="clips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $asset;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimelineFormat", inversedBy="clips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $format;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="clips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $timeline;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $lane;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BRoll", mappedBy="clip")
     */
    private $bRolls;

    public function __construct()
    {
        $this->bRolls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getTrackOffset()
    {
        return $this->track_offset;
    }

    public function setTrackOffset($track_offset): self
    {
        $this->track_offset = $track_offset;

        return $this;
    }

    public function setFromXml(\SimpleXMLElement $splineItem, Timeline $timeline): self
    {

        $formats = [];
        foreach ($timeline->getTimelineFormats() as $timelineFormat) {
            $formats[$timelineFormat->getCode()] = $timelineFormat;
        }



        foreach ($splineItem->attributes() as $var=>$val) {
            $skip = false;
            switch ($var) {
                case 'ref':
                    $val = $timeline->getAssetByCode($val);
                    $var = 'asset';
                    break;
                case 'format':
                    $val = $timeline->getFormatByCode($val);
                    break;
                case 'id':
                case 'tcFormat':
                case 'enabled':
                    $skip = true;
                    continue;
                    break;
                case 'offset':
                    $var = 'trackOffset';
                case 'duration':
                case 'start':
                    $val = (Timeline::fractionalSecondsToTime($val));
                    break;
                case 'hasVideo':
                case 'hasAudio':
                    $val = (bool)$val;
                    break;
                case 'audioSources':
                case 'audioChannels':
                    $val = (int)$val;
                    break;

                default:
            }
            if (!$skip) {
                $method = 'set' . $var;
                $this->$method($val);
            }

        }

        $this
            ->setTrackOffset($splineItem->getName())
            ->setType($splineItem->getName())
            ->setName($splineItem['name'])
            ->setStart($this->fractionalSecondsToTime($splineItem['start']))
            ->setDuration($this->fractionalSecondsToTime($splineItem['duration']))
            ->setTrackOffset($this->fractionalSecondsToTime($splineItem['offset']))
        ;

        //
        return $this;

    }

    private function fractionalSecondsToTime($str) {
        return Timeline::fractionalSecondsToTime($str);
    }


    public function getAsset(): ?TimelineAsset
    {
        return $this->asset;
    }

    public function setAsset(?TimelineAsset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getFormat(): ?TimelineFormat
    {
        return $this->format;
    }

    public function setFormat(?TimelineFormat $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getTimeline(): ?Timeline
    {
        return $this->timeline;
    }

    public function setTimeline(?Timeline $timeline): self
    {
        $this->timeline = $timeline;

        return $this;
    }

    public function getLane(): ?string
    {
        return $this->lane;
    }

    public function setLane(?string $lane): self
    {
        $this->lane = $lane;

        return $this;
    }

    /**
     * @return Collection|BRoll[]
     */
    public function getBRolls(): Collection
    {
        return $this->bRolls;
    }

    public function addBRoll(BRoll $bRoll): self
    {
        if (!$this->bRolls->contains($bRoll)) {
            $this->bRolls[] = $bRoll;
            $bRoll->setClip($this);
        }

        return $this;
    }

    public function removeBRoll(BRoll $bRoll): self
    {
        if ($this->bRolls->contains($bRoll)) {
            $this->bRolls->removeElement($bRoll);
            // set the owning side to null (unless already changed)
            if ($bRoll->getClip() === $this) {
                $bRoll->setClip(null);
            }
        }

        return $this;
    }

}
