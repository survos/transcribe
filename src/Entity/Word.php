<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WordRepository")
 * @ORM\Table(name="word",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="medix_idx_unique", columns={"media_id", "idx"})}
 *     )
 * @UniqueEntity(fields={"media","idx"})
 */
class Word
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $word;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", inversedBy="words")
     * @ORM\JoinColumn(nullable=false)
     */
    private $media;

    /**
     * @ORM\Column(type="float", precision=1)
     */
    private $startTime;

    /**
     * @ORM\Column(type="float", precision=1)
     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $endPunctuation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Marker", inversedBy="words")
     */
    private $marker;

    /**
     * @ORM\Column(type="integer")
     */
    private $idx;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
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

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    public function setStartTime(float $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    public function setEndTime(float $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getEndPunctuation(): ?string
    {
        return $this->endPunctuation;
    }

    public function setEndPunctuation(?string $endPunctuation): self
    {
        $this->endPunctuation = $endPunctuation;

        return $this;
    }

    public function getMarker(): ?Marker
    {
        return $this->marker;
    }

    public function setMarker(?Marker $marker): self
    {
        $this->marker = $marker;

        return $this;
    }

    public function getIdx(): ?int
    {
        return $this->idx;
    }

    public function setIdx(int $idx): self
    {
        $this->idx = $idx;

        return $this;
    }
}
