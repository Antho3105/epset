<?php

namespace App\Entity;

use App\Repository\VisibleCourseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisibleCourseRepository::class)]
class VisibleCourse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'visibleCourses')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'visibleCourses')]
    #[ORM\JoinColumn(nullable: false)]
    private $Course;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCourse(): ?Course
    {
        return $this->Course;
    }

    public function setCourse(?Course $Course): self
    {
        $this->Course = $Course;

        return $this;
    }
    public function __toString(): string
    {
        return $this->getUser()->getLastName() . ' ' . $this->getUser()->getFirstName() ;
    }


}
