<?php

namespace App\Entity;

use App\Repository\ResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
class Result
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $testDate;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $viewedQuestion;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $answeredQuestion;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $score;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $token;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private $survey;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private $candidate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTestDate(): ?\DateTimeInterface
    {
        return $this->testDate;
    }

    public function setTestDate(\DateTimeInterface $testDate): self
    {
        $this->testDate = $testDate;

        return $this;
    }

    public function getViewedQuestion(): ?int
    {
        return $this->viewedQuestion;
    }

    public function setViewedQuestion(?int $viewedQuestion): self
    {
        $this->viewedQuestion = $viewedQuestion;

        return $this;
    }

    public function getAnsweredQuestion(): ?int
    {
        return $this->answeredQuestion;
    }

    public function setAnsweredQuestion(?int $answeredQuestion): self
    {
        $this->answeredQuestion = $answeredQuestion;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }
}
