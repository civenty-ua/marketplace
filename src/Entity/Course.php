<?php

namespace App\Entity;

use RuntimeException;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\{
    TimestampableInterface,
    TranslatableInterface,
};
use Knp\DoctrineBehaviors\Model\{
    Timestampable\TimestampableTrait,
    Translatable\TranslatableTrait,
};
use App\Repository\CourseRepository;
use App\Traits\{
    EmptyField,
    Trans,
};
use App\Service\FileManager\FileManagerInterface;
use App\Validator as AcmeAssert;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 * @AcmeAssert\TranslationLength()
 * @AcmeAssert\OldUserCountConstraint()
 */
class Course extends Item implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @ORM\OneToMany(targetEntity=CoursePart::class, mappedBy="course", cascade={"persist", "remove"})
     */
    private $courseParts;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $personalConsalting;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="courseBanner")
     */
    private $bannerCategories;

    public function __construct()
    {
        parent::__construct();
        $this->setRegistrationRequired(true);
        $this->courseParts = new ArrayCollection();
        $this->bannerCategories = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getTypeItem(): string
    {
        return self::COURSE;
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
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

    /**
     * @return Collection|CoursePart[]
     */
    public function getCourseParts(): Collection
    {
        $cp = $this->courseParts;
        foreach ($cp as $item) {
            if ($item instanceof Lesson && $item->getActive() == false)
                $this->removeCoursePart($item);
        }
        return $cp;
    }

    public function getCoursePartsSort(): Collection {
        $cp = $this->getCourseParts();
        $iterator = $cp->getIterator();
        $iterator->uasort(function ($first, $second) {
            $f = $first->getCoursePartSorts();
            $s = $second->getCoursePartSorts();
            $firstSort = 99999;
            $secondSort = 99999;
            //TODO Убрать костыль с нулевым элементом если связь CoursePart с Course изменится на ManyToMany
            foreach ($f as $fTmp) {
                if ($fTmp->getCourse()->getId() == $this->getId()) {
                    $firstSort = $fTmp->getSort();
                }
            }
            foreach ($s as $sTmp) {
                if ($sTmp->getCourse()->getId() == $this->getId()) {
                    $secondSort = $sTmp->getSort();
                }
            }
            return (int) $firstSort > (int) $secondSort ? 1 : -1;
        });
        return new ArrayCollection($iterator->getArrayCopy());
    }

    public function getActiveCourseParts()
    {
        return $cp;
    }

    public function addCoursePart(CoursePart $coursePart): self
    {
        if (!$this->courseParts->contains($coursePart)) {
            $this->courseParts[] = $coursePart;
            $coursePart->setCourse($this);
        }

        return $this;
    }

    public function removeCoursePart(CoursePart $coursePart): self
    {
        if ($this->courseParts->removeElement($coursePart)) {
            // set the owning side to null (unless already changed)
            if ($coursePart->getCourse() === $this) {
                $coursePart->setCourse(null);
            }
        }

        return $this;
    }

    public function getPersonalConsalting(): ?bool
    {
        return $this->personalConsalting;
    }

    public function setPersonalConsalting(?bool $personalConsalting): self
    {
        $this->personalConsalting = $personalConsalting;

        return $this;
    }

    public function getCountLesson()
    {
        $count = 0;
        foreach ($this->courseParts as $part) {
            $count = $count + $part->getCountLesson();
        }

        return $count;
    }

    /**
     * @param int $currentId
     *
     * @return Lesson|null
     */
    public function getNextLesson(int $currentId): ?Lesson
    {
        $lessons = $this->getAllLessons();
        $keyTmp = $this->getCurrentKey($lessons, $currentId);
        if (isset($lessons[$keyTmp + 1])) {
            return $lessons[$keyTmp + 1];
        } else {
            return null;
        }
    }

    /**
     * @param int $currentId
     *
     * @return Lesson|null
     */
    public function getPrevLesson(int $currentId): ?Lesson
    {
        $lessons = $this->getAllLessons();
        $keyTmp = $this->getCurrentKey($lessons, $currentId);
        if (isset($lessons[$keyTmp - 1])) {
            return $lessons[$keyTmp - 1];
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    private function getAllLessons(): array
    {
        $lessons = [];
        /** @var CoursePart $part */
        foreach ($this->getCoursePartsSort() as $part) {
            if (method_exists($part, 'getLessons')) {
                foreach ($part->getLessonsSort() as $lesson) {
                    $lessons[] = $lesson;
                }
            } else {
                $lessons[] = $part;
            }
        }

        return $lessons;
    }

    /**
     * @param $lessons
     * @param $currentId
     *
     * @return int
     */
    private function getCurrentKey($lessons, $currentId): int
    {
        $keyTmp = 0;
        foreach ($lessons as $key => $lesson) {
            $keyTmp = $key;
            if ($lesson->getId() == $currentId) {
                break;
            }
        }

        return $keyTmp;
    }

    /**
     * @inheritDoc
     */
    public function getCopy(
        ParameterBagInterface $parameters,
        FileManagerInterface  $fileManager
    ): self
    {
        $result = parent::getCopy($parameters, $fileManager);

        foreach ($this->getCourseParts() as $coursePart) {
            $result->addCoursePart($coursePart->getCopy());
        }
        $result->setPersonalConsalting($this->getPersonalConsalting());
        $result->copyTranslations($this);

        if ($this->getImageName()) {
            $kernelRoot = $parameters->get('kernel.project_dir');
            $imagesDirectoryPath = $parameters->get('app.entity.files.course');
            $imagesDirectory = new SplFileInfo($kernelRoot . $imagesDirectoryPath);
            $image = new SplFileInfo(
                $imagesDirectory->getPathname() .
                DIRECTORY_SEPARATOR .
                $this->getImageName()
            );

            try {
                $imageCopy = $fileManager->copyEntityFile($image, $imagesDirectory);
                $result->setImageName($imageCopy->getFilename());
            } catch (RuntimeException $exception) {

            }
        }
        return $result;
    }

    /**
     * @return mixed
     */
    public function getBannerCategories()
    {
        return $this->bannerCategories;
    }

    public function addBannerCategory(Category $category): self
    {
        if (!$this->bannerCategories->contains($category)) {
            $this->bannerCategories[] = $category;
            $category->setCourseBanner($this);
        }

        return $this;
    }

    public function removeBannerCategory(Category $category): self
    {
        if ($this->bannerCategories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCourseBanner() === $this) {
                $category->setCourseBanner(null);
            }
        }

        return $this;
    }
}