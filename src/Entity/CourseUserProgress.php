<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CourseUserProgressRepository;
/**
 * @ORM\Entity(repositoryClass=CourseUserProgressRepository::class)
 */
class CourseUserProgress
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $completed = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

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

    public function getCompleted(): bool
    {
        return $this->completed ?? false;
    }

    public function setCompleted(?bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function getNotified(): ?bool
    {
        return $this->notified ?? false;
    }

    public function setNotified(?bool $notified): self
    {
        $this->notified = $notified;

        return $this;
    }
}
