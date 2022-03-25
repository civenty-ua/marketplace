<?php

namespace App\Entity;

use App\Repository\LessonModuleRepository;
use App\Traits\EmptyField;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass=LessonModuleRepository::class)
 */
class LessonModule extends CoursePart implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\OneToMany(targetEntity=Lesson::class, mappedBy="lessonModule")
     */
    private $lessons;


    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
    }

    public function getType(): string
    {
        return parent::LESSON_MODULE;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCountLesson(): int
    {
        return $this->lessons->count();
    }


    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return Collection|Lesson[]
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function getLessonsSort(): Collection {
        $cp = $this->getLessons();
        $iterator = $cp->getIterator();
        $iterator->uasort(function ($first, $second) {
            $f = $first->getLessonSorts();
            $s = $second->getLessonSorts();
            $firstSort = 99999;
            $secondSort = 99999;
            foreach ($f as $fTmp) {
                if ($fTmp->getLessonModule()->getId() == $this->getId()) {
                    $firstSort = $fTmp->getSort();
                }
            }
            foreach ($s as $sTmp) {
                if ($sTmp->getLessonModule()->getId() == $this->getId()) {
                    $secondSort = $sTmp->getSort();
                }
            }
            return (int) $firstSort > (int) $secondSort ? 1 : -1;
        });
        return new ArrayCollection($iterator->getArrayCopy());
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons[] = $lesson;
            $lesson->setLessonModule($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getLessonModule() === $this) {
                $lesson->setLessonModule(null);
            }
        }

        return $this;
    }
    public function getCopy(){
        $result = parent::getCopy();
        $result->setImage($this->getImage());
        $result->setStartDate($this->getStartDate());
        $result->copyTranslations($this);
        return $result;
    }
}
