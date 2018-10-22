<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MarkerRepository")
 */
class Marker
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", inversedBy="markers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $media;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idx;

    /**
     * @ORM\Column(type="integer")
     */
    private $first_word_index;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Word", mappedBy="marker")
     */
    private $words;

    /**
     * @ORM\Column(type="integer")
     */
    private $last_word_index;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $irrelevant;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hidden;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $startTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $endTime;

    public function __construct()
    {
        $this->words = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIdx(): ?int
    {
        return $this->idx;
    }

    public function setIdx(?int $idx): self
    {
        $this->idx = $idx;

        return $this;
    }

    public function getFirstWordIndex(): ?int
    {
        return $this->first_word_index;
    }

    public function setFirstWordIndex(int $first_word_index): self
    {
        $this->first_word_index = $first_word_index;

        return $this;
    }

    /**
     * @return Collection|Word[]
     */
    public function getWords(): Collection
    {
        return $this->words;
    }

    public function addWord(Word $word): self
    {
        if (!$this->words->contains($word)) {
            $this->words[] = $word;
            $word->setMarker($this);
        }

        return $this;
    }

    public function removeWord(Word $word): self
    {
        if ($this->words->contains($word)) {
            $this->words->removeElement($word);
            // set the owning side to null (unless already changed)
            if ($word->getMarker() === $this) {
                $word->setMarker(null);
            }
        }

        return $this;
    }

    public function getLastWordIndex(): ?int
    {
        return $this->last_word_index;
    }

    public function setLastWordIndex(int $last_word_index): self
    {
        $this->last_word_index = $last_word_index;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getIrrelevant(): ?bool
    {
        return $this->irrelevant;
    }

    public function setIrrelevant(?bool $irrelevant): self
    {
        $this->irrelevant = $irrelevant;

        return $this;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?int
    {
        return $this->endTime;
    }

    public function setEndTime(int $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }
}
