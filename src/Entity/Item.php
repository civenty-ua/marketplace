<?php
declare(strict_types=1);

namespace App\Entity;

use App\Traits\TopItem;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\ItemRepository;
use App\Service\FileManager\FileManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"article" = "Article", "course" = "Course", "webinar" = "Webinar", "other" = "Other", "occurrence" = "Occurrence" ,"news" = "News"})
 * @UniqueEntity(fields={"slug"})
 */
abstract class Item implements TimestampableInterface
{
    use TimestampableTrait;
    use TopItem;

    public const ARTICLE = 'article';
    public const COURSE = 'course';
    public const WEBINAR = 'webinar';
    public const OTHER = 'other';
    public const OCCURRENCE = 'occurrence';
    public const NEWS = 'news';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private $isActive = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $registrationRequired = false;

    /**
     * @ORM\ManyToOne(targetEntity=FeedbackForm::class, inversedBy="items")
     */
    private $feedbackForm;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="item")
     */
    private $comments;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $commentsAllowed = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $viewsAmount = 0;

    /**
     * @ORM\ManyToMany(targetEntity=Tags::class, inversedBy="items")
     */
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity=Partner::class, inversedBy="items")
     */
    private $partners;

    /**
     * @ORM\ManyToMany(targetEntity=Expert::class, inversedBy="items")
     */
    private $experts;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="items")
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity=News::class, mappedBy="similar")
     */
    private $similarNewsRelation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Range(min = 0, max = 5)
     */
    private $rating;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $newRating;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\PositiveOrZero()
     */
    private $oldUserCount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\ManyToMany(targetEntity=Crop::class, inversedBy="items")
     */
    private $crops;

    public function __construct()
    {
        $this->isActive = false;
        $this->registrationRequired = false;
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->partners = new ArrayCollection();
        $this->experts = new ArrayCollection();
        $this->crops = new ArrayCollection();
        $this->similarNewsRelation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsActive(): bool
    {
        return $this->isActive ?? false;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRegistrationRequired(): bool
    {
        return $this->registrationRequired ?? false;
    }

    public function setRegistrationRequired(bool $registrationRequired): self
    {
        $this->registrationRequired = $registrationRequired;

        return $this;
    }

    public function getFeedbackForm(): ?FeedbackForm
    {
        return $this->feedbackForm;
    }

    public function setFeedbackForm(?FeedbackForm $feedbackForm): self
    {
        $this->feedbackForm = $feedbackForm;

        return $this;
    }

    public function getViewsAmount(): int
    {
        return $this->viewsAmount ?? 0;
    }

    public function setViewsAmount(?int $viewsAmount): self
    {
        $this->viewsAmount = $viewsAmount;

        return $this;
    }

    public function increaseViewsAmount(): self
    {
        $this->setViewsAmount($this->getViewsAmount() + 1);

        return $this;
    }

    public function increaseOldUserCount(): self
    {
        $this->setOldUserCount($this->getOldUserCount() + 1);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getSimilarNewsRelation(): Collection
    {
        return $this->similarNewsRelation;
    }

    public function addSimilarNewsRelation(?Item $similar): self
    {
        if (!$this->similarNewsRelation->contains($similar)) {
            $this->similarNewsRelation[] = $similar;
        }

        return $this;
    }

    public function removeSimilarNewsRelation(self $similar): self
    {
        $this->similarNewsRelation->removeElement($similar);

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setItem($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getItem() === $this) {
                $comment->setItem(null);
            }
        }

        return $this;
    }

    public function getCommentsAllowed(): bool
    {
        return $this->commentsAllowed ?? false;
    }

    public function setCommentsAllowed(bool $commentsAllowed): self
    {
        $this->commentsAllowed = $commentsAllowed;

        return $this;
    }

    /**
     * @return Collection|Tags[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tags $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tags $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection|Partner[]
     */
    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(Partner $partner): self
    {
        if (!$this->partners->contains($partner)) {
            $this->partners[] = $partner;
        }

        return $this;
    }

    public function removePartner(Partner $partner): self
    {
        $this->partners->removeElement($partner);

        return $this;
    }

    /**
     * @return Collection|Expert[]
     */
    public function getExperts(): Collection
    {
        return $this->experts;
    }

    public function getExpertsCount(): int
    {
        return $this->experts->count();
    }

    public function addExpert(Expert $expert): self
    {
        if (!$this->experts->contains($expert)) {
            $this->experts[] = $expert;
        }

        return $this;
    }

    public function removeExpert(Expert $expert): self
    {
        $this->experts->removeElement($expert);

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return number_format((float)$this->rating, 3) ?? 0;
    }

    /**
     * @param mixed $rating
     */
    public function setRating($rating): void
    {
        $this->rating = $rating;
    }

    public function getOldUserCount(): int
    {
        return (int)$this->oldUserCount ?? 0;
    }

    /**
     * @param mixed $oldUserCount
     */
    public function setOldUserCount($oldUserCount)
    {
        $this->oldUserCount = $oldUserCount;
    }

    /**
     * @return mixed
     */
    public function getNewRating()
    {
        return $this->newRating;
    }

    /**
     * @param mixed $newRating
     */
    public function setNewRating($newRating): void
    {
        $this->newRating = $newRating;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getClassName()
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * @return Collection|Crop[]
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }

    public function addCrop(Crop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
        }

        return $this;
    }

    public function removeCrop(Crop $crop): self
    {
        $this->crops->removeElement($crop);

        return $this;
    }

    /**
     * Get item copy.
     *
     * @param ParameterBagInterface $parameters Parameters container.
     * @param FileManagerInterface $fileManager Files manager, used for files copy
     *
     * @return self Entity copy.
     */
    public function getCopy(
        ParameterBagInterface $parameters,
        FileManagerInterface $fileManager
    ): self {
        $result = new static();

        $result->setIsActive($this->getIsActive());
        $result->setSlug("{$this->getSlug()}-copy");
        $result->setRegistrationRequired($this->getRegistrationRequired());
        $result->setFeedbackForm($this->getFeedbackForm());
        $result->setCommentsAllowed($this->getCommentsAllowed());
        $result->setCategory($this->getCategory());
        $result->setStartDate($this->getStartDate());

        foreach ($this->getTags() as $tag) {
            $result->addTag($tag);
        }
        foreach ($this->getPartners() as $partner) {
            $result->addPartner($partner);
        }
        foreach ($this->getExperts() as $expert) {
            $result->addExpert($expert);
        }
        foreach ($this->getCrops() as $crop) {
            $result->addCrop($crop);
        }

        return $result;
    }

    /**
     * Get item type.
     *
     * @return string Item type.
     */
    abstract public function getTypeItem(): string;

    /**
     * @param $filterNameList
     *
     * @return array
     */
    public static function getItemTypeByFilterName($filterNameList): array
    {
        $result = [];
        foreach ($filterNameList as $filterName) {
            switch ($filterName) {
                case 'course':
                    $result[] = Course::class;
                    break;
                case 'webinar':
                    $result[] = Webinar::class;
                    break;
                case 'article':
                    $result[] = Article::class;
                    break;
                case 'other':
                    $result[] = Other::class;
                    break;
                case 'occurrence':
                    $result[] = Occurrence::class;
                    break;
                case 'news':
                    $result[] = News::class;
                    break;
            }
        }

        return $result;
    }
}
