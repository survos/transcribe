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
     * @ORM\Column(type="string", length=128)
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
        $this->start_word = trim($start_word);

        return $this;
    }

    public function highlightedNote($before='<b>', $after='</b>')
    {
        $note = $this->getMarker()->getNote();
        $start = $this->getStartWord();
        if ($startPos = strpos($note, $start)) {

            if (preg_match(sprintf('/^(.*?)(%s.*)$/', $start), $note, $m)) {
                return $m[1] . $before . $m[2] . $after;
            }
        }

        return $note;
    }

    public function calculateStartWordTime()
    {
        // maybe it's in the words..
        foreach ($this->getMarker()->getWords() as $word) {
            if ($word->getWord() == $this->getStartWord()) {
                return $word->getStartTime();
            }
        }
        $marker = $this->getMarker();
        if ($this->getStartWord()) {
            $startPosition = strpos($marker->getNote(), $this->getStartWord());
            if ($startPosition !== false) {
                $startTime = $marker->getDuration() * ($startPosition / strlen($marker->getNote()));
            }
        }

        if (!isset($startTime)) {
            // start halfway through, hack
            $startTime = $marker->getDuration() / 2;
        }

        return $startTime;
    }

}
