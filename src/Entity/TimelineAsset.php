<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TimelineAssetRepository")
 */
class TimelineAsset
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $src;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $audio_sources;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $has_video;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=1, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $has_audio;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $audio_channels;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TimelineFormat", inversedBy="timelineAssets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $format;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Clip", mappedBy="asset", orphanRemoval=true)
     */
    private $clips;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="timelineAssets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $timeline;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $start;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", inversedBy="timelineAssets")
     */
    private $media;

    public function __construct()
    {
        $this->clips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): self
    {
        $this->src = $src;

        return $this;
    }

    public function getAudioSources(): ?int
    {
        return $this->audio_sources;
    }

    public function setAudioSources(?int $audio_sources): self
    {
        $this->audio_sources = $audio_sources;

        return $this;
    }

    public function getHasVideo(): ?bool
    {
        return $this->has_video;
    }

    public function setHasVideo(?bool $has_video): self
    {
        $this->has_video = $has_video;

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

    public function getHasAudio(): ?bool
    {
        return $this->has_audio;
    }

    public function setHasAudio(?bool $has_audio): self
    {
        $this->has_audio = $has_audio;

        return $this;
    }

    public function getAudioChannels(): ?int
    {
        return $this->audio_channels;
    }

    public function setAudioChannels(?int $audio_channels): self
    {
        $this->audio_channels = $audio_channels;

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

    /**
     * @return Collection|Clip[]
     */
    public function getClips(): Collection
    {
        return $this->clips;
    }

    public function addClip(Clip $clip): self
    {
        if (!$this->clips->contains($clip)) {
            $this->clips[] = $clip;
            $clip->setAsset($this);
        }

        return $this;
    }

    public function removeClip(Clip $clip): self
    {
        if ($this->clips->contains($clip)) {
            $this->clips->removeElement($clip);
            // set the owning side to null (unless already changed)
            if ($clip->getAsset() === $this) {
                $clip->setAsset(null);
            }
        }

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

    public function setFromXml(\SimpleXMLElement $splineItem, Timeline $timeline): self
    {
        // <asset src="file://localhost/C:/JUFJ/MAS/mas-1.MOV" start="0/1s" audioSources="1" name="mas-1.MOV" hasVideo="1" format="r1" duration="7007/250s" hasAudio="1" audioChannels="2" id="r2"/>
        $this
            ->setName($splineItem['name'])
            ->setCode($splineItem['id']);

        $formats = [];
        foreach ($timeline->getTimelineFormats() as $timelineFormat) {
            $formats[$timelineFormat->getCode()] = $timelineFormat;
        }

        foreach ($splineItem->attributes() as $var=>$val) {
            $skip = false;
            switch ($var) {
                case 'format':
                    $val = $formats[(string)$val];
                    break;
                case 'id':
                    $skip = true;
                    break;
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

        ;
        if (!empty($splineItem['width'])) {
            $this
                ->setWidth((int)$splineItem['width'])
                ->setHeight((int)$splineItem['height']);
        }

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

    public function isPhoto(): bool
    {
        return in_array(strtolower(pathinfo($this->getSrc(), PATHINFO_EXTENSION)), ['jpg', 'png']);

    }

    public function __toString()
    {
        return $this->getCode();
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): self
    {
        $this->media = $media;

        return $this;
    }

}
