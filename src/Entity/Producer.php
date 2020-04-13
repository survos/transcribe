<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProducerRepository")
 */
class Producer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @SerializedName("@id")
     */
    private $id;

    /**
     * @param mixed $id
     * @return Producer
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Property", mappedBy="producer", orphanRemoval=true)
     * @SerializedName("property")
     */
    private $properties;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mlt", inversedBy="producers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mlt;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     * @SerializedName("@in")
     */
    private $inTime;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $outTime;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->mlts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return Collection|Property[]
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): self
    {
        if (!$this->properties->contains($property)) {
            $this->properties[] = $property;
            $property->setProducer($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->contains($property)) {
            $this->properties->removeElement($property);
            // set the owning side to null (unless already changed)
            if ($property->getProducer() === $this) {
                $property->setProducer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mlt[]
     */
    public function getMlts(): Collection
    {
        return $this->mlts;
    }

    public function addMlt(Mlt $mlt): self
    {
        if (!$this->mlts->contains($mlt)) {
            $this->mlts[] = $mlt;
            $mlt->setProducers($this);
        }

        return $this;
    }

    public function removeMlt(Mlt $mlt): self
    {
        if ($this->mlts->contains($mlt)) {
            $this->mlts->removeElement($mlt);
            // set the owning side to null (unless already changed)
            if ($mlt->getProducers() === $this) {
                $mlt->setProducers(null);
            }
        }

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

    public function getInTime(): ?string
    {
        return $this->inTime;
    }

    public function setInTime(?string $inTime): self
    {
        $this->inTime = $inTime;

        return $this;
    }

    public function getOutTime(): ?string
    {
        return $this->outTime;
    }

    public function setOutTime(?string $outTime): self
    {
        $this->outTime = $outTime;

        return $this;
    }
}
