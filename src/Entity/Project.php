<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $base_path;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Media", mappedBy="project", orphanRemoval=true)
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Timeline", mappedBy="project", orphanRemoval=true)
     */
    private $timelines;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->timelines = new ArrayCollection();
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

    public function getBasePath(): ?string
    {
        return $this->base_path;
    }

    public function setBasePath(string $base_path): self
    {
        $this->base_path = $base_path;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setProject($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->contains($medium)) {
            $this->media->removeElement($medium);
            // set the owning side to null (unless already changed)
            if ($medium->getProject() === $this) {
                $medium->setProject(null);
            }
        }

        return $this;
    }

    public function getBucketName(): string
    {
        return 'survos_' . $this->getCode();
    }

    public function __toString()
    {
        return $this->getCode();
    }

    // @todo: should be code...
    public function rp($addl=[])
    {
        return array_merge($addl, ['code' => $this->getCode()]);
    }

    /**
     * @return Collection|Timeline[]
     */
    public function getTimelines(): Collection
    {
        return $this->timelines;
    }

    public function addTimeline(Timeline $timeline): self
    {
        if (!$this->timelines->contains($timeline)) {
            $this->timelines[] = $timeline;
            $timeline->setProject($this);
        }

        return $this;
    }

    public function getByType($type)
    {
        return $this->getMedia()->filter(function (Media $media) use ($type) { return $media->getType() == $type; });
    }

    public function removeTimeline(Timeline $timeline): self
    {
        if ($this->timelines->contains($timeline)) {
            $this->timelines->removeElement($timeline);
            // set the owning side to null (unless already changed)
            if ($timeline->getProject() === $this) {
                $timeline->setProject(null);
            }
        }

        return $this;
    }


}
