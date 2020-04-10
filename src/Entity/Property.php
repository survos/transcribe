<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyRepository")
 */
class Property
{
    /**
     * @ ORM\Id()
     * @ ORM\GeneratedValue()
     * @ ORM\Column(type="integer")
    private $id;
     */

    /**
     * @ORM\Column(type="text", nullable=true)
     * @SerializedName("#")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Producer", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
    private $producer;
     */

    /**
     * @ORM\Column(type="string", length=255)
     * @SerializedName("@name")
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getProducer(): ?Producer
    {
        return $this->producer;
    }

    public function setProducer(?Producer $producer): self
    {
        $this->producer = $producer;

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
}
