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
}
