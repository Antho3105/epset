<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
class Survey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 15)]
    private $ref;

    #[ORM\Column(type: 'string', length: 1500)]
    private $detail;

    #[ORM\Column(type: 'integer')]
    private $difficulty;

    #[ORM\Column(type: 'smallint')]
    private $questionTimer;

    #[ORM\Column(type: 'boolean')]
    private $ordered;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getQuestionTimer(): ?int
    {
        return $this->questionTimer;
    }

    public function setQuestionTimer(int $questionTimer): self
    {
        $this->questionTimer = $questionTimer;

        return $this;
    }

    public function isOrdered(): ?bool
    {
        return $this->ordered;
    }

    public function setOrdered(bool $ordered): self
    {
        $this->ordered = $ordered;

        return $this;
    }
}
