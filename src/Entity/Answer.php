<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $value;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private $question;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deleteDate;

    #[ORM\Column(type: 'boolean')]
    private $isRightAnswer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getDeleteDate(): ?\DateTimeInterface
    {
        return $this->deleteDate;
    }

    public function setDeleteDate(?\DateTimeInterface $deleteDate): self
    {
        $this->deleteDate = $deleteDate;

        return $this;
    }

    public function isIsRightAnswer(): ?bool
    {
        return $this->isRightAnswer;
    }

    public function setIsRightAnswer(bool $isRightAnswer): self
    {
        $this->isRightAnswer = $isRightAnswer;

        return $this;
    }
}
