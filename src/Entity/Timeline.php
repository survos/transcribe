<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TimelineRepository")
 */
class Timeline
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="timelines")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Marker", inversedBy="timelines")
     */
    private $markers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gap_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $max_duration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimelineFormat", mappedBy="timeline", orphanRemoval=true)
     */
    private $timelineFormats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Clip", mappedBy="timeline", orphanRemoval=true)
     */
    private $clips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TimelineAsset", mappedBy="timeline", orphanRemoval=true)
     */
    private $timelineAssets;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalDuration;

    public function __construct()
    {
        $this->markers = new ArrayCollection();
        $this->timelineFormats = new ArrayCollection();
        $this->clips = new ArrayCollection();
        $this->timelineAssets = new ArrayCollection();
        $this->max_duration = 180;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $code = str_replace('(', '_', $code);
        $code = str_replace(')', '', $code);
        $this->code = $code;

        return $this;
    }

    public function calcDuration()
    {
        $duration = 0;
        foreach ($this->getClips() as $clip) {
            $duration += $clip->getDuration();
        }
        return $duration;
    }

    /**
     * @return Collection|Marker[]
     */
    public function getMarkers(): Collection
    {
        return $this->markers;
    }

    public function addMarker(Marker $marker): self
    {
        if (!$this->markers->contains($marker)) {
            $this->markers[] = $marker;
        }

        return $this;
    }

    public function removeMarker(Marker $marker): self
    {
        if ($this->markers->contains($marker)) {
            $this->markers->removeElement($marker);
        }

        return $this;
    }

    public function getGapTime(): ?int
    {
        return $this->gap_time;
    }

    public function setGapTime(?int $gap_time): self
    {
        $this->gap_time = $gap_time;

        return $this;
    }

    public function getMaxDuration(): ?int
    {
        return $this->max_duration;
    }

    public function setMaxDuration(?int $max_duration): self
    {
        $this->max_duration = $max_duration;

        return $this;
    }

    /**
     * @return Collection|TimelineFormat[]
     */
    public function getTimelineFormats(): Collection
    {
        return $this->timelineFormats;
    }

    public function addTimelineFormat(TimelineFormat $timelineFormat): self
    {
        if (!$this->timelineFormats->contains($timelineFormat)) {
            $this->timelineFormats[] = $timelineFormat;
            $timelineFormat->setTimeline($this);
        }

        return $this;
    }

    public function getAssetByCode($code): ?TimelineAsset
    {
        foreach ($this->getTimelineAssets() as $asset) {
            if ($asset->getCode() == $code) {
                return $asset;
            }
        }
        return null;
    }

    public function getFormatByCode($code): ?TimelineFormat
    {
        foreach ($this->getTimelineFormats() as $format) {
            if ($format->getCode() == $code) {
                return $format;
            }
        }
        return null;
    }

    public function removeTimelineFormat(TimelineFormat $timelineFormat): self
    {
        if ($this->timelineFormats->contains($timelineFormat)) {
            $this->timelineFormats->removeElement($timelineFormat);
            // set the owning side to null (unless already changed)
            if ($timelineFormat->getTimeline() === $this) {
                $timelineFormat->setTimeline(null);
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
            $clip->setTimeline($this);
        }

        return $this;
    }

    public function removeClip(Clip $clip): self
    {
        if ($this->clips->contains($clip)) {
            $this->clips->removeElement($clip);
            // set the owning side to null (unless already changed)
            if ($clip->getTimeline() === $this) {
                $clip->setTimeline(null);
            }
        }

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
            $timelineAsset->setTimeline($this);
        }

        return $this;
    }

    public function removeTimelineAsset(TimelineAsset $timelineAsset): self
    {
        if ($this->timelineAssets->contains($timelineAsset)) {
            $this->timelineAssets->removeElement($timelineAsset);
            // set the owning side to null (unless already changed)
            if ($timelineAsset->getTimeline() === $this) {
                $timelineAsset->setTimeline(null);
            }
        }

        return $this;
    }

    static public function fractionalSecondsToTime($s)
    {
        // format X/ys, e.g. 40245/5000s, for accurate frame count.
        // drop the s, split the string
        if (preg_match('|(\d+)/(\d+)s|', $s, $m)) {
            list($dummy, $num, $denom) = $m;
            return $num/$denom;
        }
    }

    public function getTotalDuration()
    {
        return $this->totalDuration;
    }

    public function setTotalDuration($totalDuration): self
    {
        $this->totalDuration = $totalDuration;

        return $this;
    }




}
