<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BRollRepository")
 */
class BRoll
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=48)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Marker", inversedBy="bRolls")
     * @ORM\JoinColumn(nullable=false)
     */
    private $marker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", inversedBy="bRolls")
     * @ORM\JoinColumn(nullable=true)
     */
    private $media;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clip", inversedBy="bRolls")
     */
    private $clip;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $start_word;

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

    public function getMarker(): ?Marker
    {
        return $this->marker;
    }

    public function setMarker(?Marker $marker): self
    {
        $this->marker = $marker;

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

    public function getClip(): ?Clip
    {
        return $this->clip;
    }

    public function setClip(?Clip $clip): self
    {
        $this->clip = $clip;

        return $this;
    }

    public function getStartWord(): ?string
    {
        return $this->start_word;
    }

    public function setStartWord(?string $start_word): self
    {
        $this->start_word = $start_word;

        return $this;
    }

    public function HighlightedNote($before='<b>', $after='</b>')
    {
        return str_replace($this->getStartWord(), $before . $this->getStartWord(), $this->getMarker()->getNote()) . $after;
    }


}
