<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;


    #[ORM\Column(type: 'text')]
    private $question;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private $answer;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private $choice2;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private $choice3;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private $choice4;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private $choice5;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private $Survey;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deleteDate;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class)]
    private $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getChoice2(): ?string
    {
        return $this->choice2;
    }

    public function setChoice2(string $choice2): self
    {
        $this->choice2 = $choice2;

        return $this;
    }

    public function getChoice3(): ?string
    {
        return $this->choice3;
    }

    public function setChoice3(string $choice3): self
    {
        $this->choice3 = $choice3;

        return $this;
    }

    public function getChoice4(): ?string
    {
        return $this->choice4;
    }

    public function setChoice4(string $choice4): self
    {
        $this->choice4 = $choice4;

        return $this;
    }

    public function getChoice5(): ?string
    {
        return $this->choice5;
    }

    public function setChoice5(string $choice5): self
    {
        $this->choice5 = $choice5;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->Survey;
    }

    public function setSurvey(?Survey $Survey): self
    {
        $this->Survey = $Survey;

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

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }
}
