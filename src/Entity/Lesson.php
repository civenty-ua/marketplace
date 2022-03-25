<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\LessonRepository;
use App\Traits\EmptyField;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Validator as AcmeAssert;

/**
 * @ORM\Entity(repositoryClass=LessonRepository::class)
 * @AcmeAssert\TranslationLength()
 */
class Lesson extends CoursePart implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @ORM\ManyToOne(targetEntity=VideoItem::class, inversedBy="lessons")
     */
    private $videoItem;

    /**
     * @ORM\ManyToOne(targetEntity=LessonModule::class, inversedBy="lessons")
     */
    private $lessonModule;

    /**
     * @ORM\OneToMany(targetEntity=LessonSort::class, mappedBy="lesson")
     */
    private $lessonSorts;

    public function __construct()
    {
        parent::__construct();
        $this->lessonSorts = new ArrayCollection();
    }


    public function getType(): string
    {
        return parent::LESSON;
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle') ?? '';
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getVideoItem(): ?VideoItem
    {
        return $this->videoItem;
    }

    public function setVideoItem(?VideoItem $videoItem): self
    {
        $this->videoItem = $videoItem;

        return $this;
    }

    public function getCountLesson()
    {
        return 1;
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

    public function getCopy(){
        $result = parent::getCopy();
        $result->setActive($this->getActive());
        $result->setImageName($this->getImageName());
        $result->setVideoItem($this->getVideoItem());
        $result->setLessonModule($this->getLessonModule());
        $result->copyTranslations($this);
        return $result;
    }

    /**
     * @return Collection|LessonSort[]
     */
    public function getLessonSorts(): Collection
    {
        return $this->lessonSorts;
    }

    public function addLessonSort(LessonSort $lessonSort): self
    {
        if (!$this->lessonSorts->contains($lessonSort)) {
            $this->lessonSorts[] = $lessonSort;
            $lessonSort->setLesson($this);
        }

        return $this;
    }

    public function removeLessonSort(LessonSort $lessonSort): self
    {
        if ($this->lessonSorts->removeElement($lessonSort)) {
            // set the owning side to null (unless already changed)
            if ($lessonSort->getLesson() === $this) {
                $lessonSort->setLesson(null);
            }
        }

        return $this;
    }
}
