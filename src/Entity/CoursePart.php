<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CoursePartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\ItemRepository;

/**
 *
 * @ORM\Entity(repositoryClass=CoursePartRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"lesson" = "Lesson", "lesson_module" = "LessonModule"})
 */
abstract class CoursePart
{
    const LESSON = 'lesson';
    const LESSON_MODULE = 'lesson_module';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="courseParts")
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity=Expert::class)
     */
    private $expert;

    /**
     * @ORM\OneToMany(targetEntity=CoursePartSort::class, mappedBy="coursePart")
     */
    private $coursePartSorts;

    public function __construct()
    {
        $this->coursePartSorts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpert(): ?Expert
    {
        return $this->expert;
    }

    public function setExpert(?Expert $expert): self
    {
        $this->expert = $expert;

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
    public function getCopy(){
        $result = new static();

        $result->setExpert($this->getExpert());

        return $result;
    }

    /**
     * @return Collection|CoursePartSort[]
     */
    public function getCoursePartSorts(): Collection
    {
        return $this->coursePartSorts;
    }

    public function addCoursePartSort(CoursePartSort $coursePartSort): self
    {
        if (!$this->coursePartSorts->contains($coursePartSort)) {
            $this->coursePartSorts[] = $coursePartSort;
            $coursePartSort->setCoursePart($this);
        }

        return $this;
    }

    public function removeCoursePartSort(CoursePartSort $coursePartSort): self
    {
        if ($this->coursePartSorts->removeElement($coursePartSort)) {
            // set the owning side to null (unless already changed)
            if ($coursePartSort->getCoursePart() === $this) {
                $coursePartSort->setCoursePart(null);
            }
        }

        return $this;
    }
}