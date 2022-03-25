<?php

namespace App\Entity;

use App\Repository\LessonSortRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LessonSortRepository::class)
 */
class LessonSort
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Lesson::class, inversedBy="lessonSorts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lesson;

    /**
     * @ORM\ManyToOne(targetEntity=LessonModule::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $lessonModule;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): self
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getLessonModule(): ?LessonModule
    {
        return $this->lessonModule;
    }

    public function setLessonModule(?LessonModule $lessonModule): self
    {
        $this->lessonModule = $lessonModule;

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
