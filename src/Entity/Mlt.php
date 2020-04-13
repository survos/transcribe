<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MltRepository")
 */
class Mlt
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
     * @ORM\OneToMany(targetEntity="App\Entity\Profile", mappedBy="mlt", orphanRemoval=true)
     */
    private $profiles;

    /**
     * @ORM\Column(type="string", length=255)
     * @SerializedName("@root")
     */
    private $root;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @SerializedName("@LC_NUMERIC")
     */
    private $LcNumeric;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $version;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Producer", mappedBy="mlt", orphanRemoval=true)
     * @SerializedName("producers")
     */
    private $producers;

    public function __construct()
    {
        $this->profiles = new ArrayCollection();
        $this->producers = new ArrayCollection();
    }

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

    /**
     * @return Collection|Profile[]
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): self
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles[] = $profile;
            // $profile->setMlt($this);
        }

        return $this;
    }

    public function removeProfile(Profile $profile): self
    {
        if ($this->profiles->contains($profile)) {
            $this->profiles->removeElement($profile);
            // set the owning side to null (unless already changed)
            if ($profile->getMlt() === $this) {
                $profile->setMlt(null);
            }
        }

        return $this;
    }

    public function getRoot(): ?string
    {
        return $this->root;
    }

    public function setRoot(string $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getLcNumeric(): ?string
    {
        return $this->LcNumeric;
    }

    public function setLcNumeric(?string $LcNumeric): self
    {
        $this->LcNumeric = $LcNumeric;

        return $this;
    }


    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Collection|Producer[]
     */
    public function getProducers(): Collection
    {
        return $this->producers;
    }

    public function addProducer(Producer $producer): self
    {
        if (!$this->producers->contains($producer)) {
            $this->producers[] = $producer;
            $producer->setMlt($this);
        }

        return $this;
    }

    public function removeProducer(Producer $producer): self
    {
        if ($this->producers->contains($producer)) {
            $this->producers->removeElement($producer);
            // set the owning side to null (unless already changed)
            if ($producer->getMlt() === $this) {
                $producer->setMlt(null);
            }
        }

        return $this;
    }
}
