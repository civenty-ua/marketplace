<?php

namespace App\Entity;

use App\Repository\CoursePartSortRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CoursePartSortRepository::class)
 */
class CoursePartSort
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CoursePart::class, inversedBy="coursePartSorts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coursePart;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $course;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoursePart(): ?CoursePart
    {
        return $this->coursePart;
    }

    public function setCoursePart(?CoursePart $coursePart): self
    {
        $this->coursePart = $coursePart;

        return $this;
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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }
}
