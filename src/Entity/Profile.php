<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $attributes = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mlt", inversedBy="profiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mlt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $frameRateNum;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $sampleAspectNum;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getMlt(): ?Mlt
    {
        return $this->mlt;
    }

    public function setMlt(?Mlt $mlt): self
    {
        $this->mlt = $mlt;

        return $this;
    }

    public function getFrameRateNum(): ?int
    {
        return $this->frameRateNum;
    }

    public function setFrameRateNum(?int $frameRateNum): self
    {
        $this->frameRateNum = $frameRateNum;

        return $this;
    }

    public function getSampleAspectNum(): ?string
    {
        return $this->sampleAspectNum;
    }

    public function setSampleAspectNum(?string $sampleAspectNum): self
    {
        $this->sampleAspectNum = $sampleAspectNum;

        return $this;
    }
}
