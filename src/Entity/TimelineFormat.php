<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Time;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TimelineFormatRepository")
 */
class TimelineFormat
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
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $frame_duration_string;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Timeline", inversedBy="timelineFormats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $timeline;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimelineAsset", mappedBy="format", orphanRemoval=true)
     */
    private $timelineAssets;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Clip", mappedBy="format", orphanRemoval=true)
     */
    private $clips;

    public function __construct()
    {
        $this->timelineAssets = new ArrayCollection();
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

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getFrameDurationString(): ?string
    {
        return $this->frame_duration_string;
    }

    public function setFrameDurationString(?string $frame_duration_string): self
    {
        $this->frame_duration_string = $frame_duration_string;

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

    /**
     * @return Collection|TimelineAsset[]
     */
    public function getTimelineAssets(): Collection
    {
        return $this->timelineAssets;
    }

    public function addTimelineAsset(TimelineAsset $timelineAsset): self
    {
        if (!$this->timelineAssets->contains($timelineAsset)) {
            $this->timelineAssets[] = $timelineAsset;
            $timelineAsset->setFormat($this);
        }

        return $this;
    }

    public function removeTimelineAsset(TimelineAsset $timelineAsset): self
    {
        if ($this->timelineAssets->contains($timelineAsset)) {
            $this->timelineAssets->removeElement($timelineAsset);
            // set the owning side to null (unless already changed)
            if ($timelineAsset->getFormat() === $this) {
                $timelineAsset->setFormat(null);
            }
        }

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
            $clip->setFormat($this);
        }

        return $this;
    }

    public function removeClip(Clip $clip): self
    {
        if ($this->clips->contains($clip)) {
            $this->clips->removeElement($clip);
            // set the owning side to null (unless already changed)
            if ($clip->getFormat() === $this) {
                $clip->setFormat(null);
            }
        }

        return $this;
    }

    public function setFromXml(\SimpleXMLElement $splineItem, Timeline $timeline): self
    {
        // <format name="FFVideoFormat1080p2997" width="1920" frameDuration="1001/30000s" height="1080" id="r0"/>
         $this
            ->setName($splineItem['name'])
            ->setCode($splineItem['id'])
            ->setFrameDurationString(Timeline::fractionalSecondsToTime($splineItem['frameDuration']))
        ;
         if (!empty($splineItem['width'])) {
             $this
                 ->setWidth((int)$splineItem['width'])
                 ->setHeight((int)$splineItem['height']);
         }

        return $this;

    }

    public function __toString()
    {
        return $this->getCode();
    }

}
