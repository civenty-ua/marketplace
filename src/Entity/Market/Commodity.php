<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use App\Traits\Meta;
use Cocur\Slugify\Slugify;
use DateTimeInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Market\CommodityRepository;
use App\Entity\User;
/**
 * @ORM\Entity(repositoryClass=CommodityRepository::class)
 * @ORM\Table(name="market_commodity",indexes={
 *     @ORM\Index(name="search_idx", columns={"slug"})
 * })
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"product" = "CommodityProduct", "kit" = "CommodityKit", "service" = "CommodityService"})
 */
abstract class Commodity implements TimestampableInterface
{
    use TimestampableTrait,
        SoftDeletableTrait,
        Meta;

    public const TYPE_PRODUCT           = 'product';
    public const TYPE_SERVICE           = 'service';
    public const TYPE_KIT               = 'kit';

    public const REQUIRED_USER_ROLES    = [];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Title attribute must be at least {{ limit }} characters long",
     *      maxMessage = "Title attribute cannot be longer than {{ limit }} characters"
     * )
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Assert\PositiveOrZero()
     */
    private ?float $price = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity=CommodityAttributeValue::class,
     *     mappedBy="commodity",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     *     )
     */
    private Collection $commodityAttributesValues;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isActive = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $activeFrom = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $activeTo = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commodities")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\ManyToMany(targetEntity=Phone::class)
     * @ORM\JoinTable(name="market_commodity_user_display_phone")
     */
    private Collection $userDisplayPhones;

    /**
     * @ORM\OneToMany(targetEntity=CommodityFavorite::class, mappedBy="commodity", orphanRemoval=true)
     */
    private Collection $favorites;

    /**
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $viewsAmount = null;

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = (new Slugify())->slugify($slug);
    }

    public function __construct()
    {
        $this->commodityAttributesValues = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->userDisplayPhones = new ArrayCollection();
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PRODUCT,
            self::TYPE_SERVICE,
            self::TYPE_KIT,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|CommodityAttributeValue[]
     */
    public function getCommodityAttributesValues(): Collection
    {
        return $this->commodityAttributesValues;
    }

    public function addCommodityAttributeValue(CommodityAttributeValue $commodityAttributeValue): self
    {
        if (!$this->commodityAttributesValues->contains($commodityAttributeValue)) {
            $this->commodityAttributesValues[] = $commodityAttributeValue;
            $commodityAttributeValue->setCommodity($this);
        }

        return $this;
    }

    public function removeCommodityAttributeValue(CommodityAttributeValue $commodityAttributeValue): self
    {
        if ($this->commodityAttributesValues->removeElement($commodityAttributeValue)) {
            if ($commodityAttributeValue->getCommodity() === $this) {
                $commodityAttributeValue->setCommodity(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getActiveFrom(): ?DateTimeInterface
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?DateTimeInterface $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    public function getActiveTo(): ?DateTimeInterface
    {
        return $this->activeTo;
    }

    public function setActiveTo(?DateTimeInterface $activeTo): self
    {
        $this->activeTo = $activeTo;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
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
     * @return Collection|Phone[]
     */
    public function getUserDisplayPhones(): Collection
    {
        return $this->userDisplayPhones;
    }

    public function addUserDisplayPhone(Phone $userDisplayPhone): self
    {
        if (!$this->userDisplayPhones->contains($userDisplayPhone)) {
            $this->userDisplayPhones[] = $userDisplayPhone;
        }

        return $this;
    }

    public function removeUserDisplayPhone(Phone $userDisplayPhone): self
    {
        $this->userDisplayPhones->removeElement($userDisplayPhone);

        return $this;
    }
    /**
     * @return Collection|CommodityFavorite[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(CommodityFavorite $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->setCommodity($this);
        }

        return $this;
    }

    public function removeFavorite(CommodityFavorite $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            if ($favorite->getCommodity() === $this) {
                $favorite->setCommodity(null);
            }
        }

        return $this;
    }

    public function getViewsAmount(): int
    {
        return $this->viewsAmount ?? 0;
    }

    public function setViewsAmount(?int $viewsAmount): void
    {
        $this->viewsAmount = $viewsAmount;
    }

    public function increaseViewsAmount(): self
    {
        $this->setViewsAmount($this->getViewsAmount() + 1);

        return $this;
    }

    abstract public function getCommodityType(): string;
}
