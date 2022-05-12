<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 15)]
    private $ref;

    #[ORM\Column(type: 'string', length: 1500)]
    private $detail;

    #[ORM\Column(type: 'string', length: 40)]
    private $level;

    #[ORM\Column(type: 'string', length: 100)]
    private $title;

    #[ORM\OneToMany(mappedBy: 'Course', targetEntity: VisibleCourse::class)]
    private $visibleCourses;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Survey::class)]
    private $surveys;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deleteDate;

    public function __construct()
    {
        $this->visibleCourses = new ArrayCollection();
        $this->surveys = new ArrayCollection();
    }

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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, VisibleCourse>
     */
    public function getVisibleCourses(): Collection
    {
        return $this->visibleCourses;
    }

    public function addVisibleCourse(VisibleCourse $visibleCourse): self
    {
        if (!$this->visibleCourses->contains($visibleCourse)) {
            $this->visibleCourses[] = $visibleCourse;
            $visibleCourse->setCourse($this);
        }

        return $this;
    }

    public function removeVisibleCourse(VisibleCourse $visibleCourse): self
    {
        if ($this->visibleCourses->removeElement($visibleCourse)) {
            // set the owning side to null (unless already changed)
            if ($visibleCourse->getCourse() === $this) {
                $visibleCourse->setCourse(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): self
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys[] = $survey;
            $survey->setCourse($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): self
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getCourse() === $this) {
                $survey->setCourse(null);
            }
        }

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
}
