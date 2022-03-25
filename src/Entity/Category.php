<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @Vich\Uploadable
 * @UniqueEntity(fields={"slug"})
 */
class Category implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $banner;

    /**
     * @Vich\UploadableField(mapping="category_banner_image", fileNameProperty="banner")
     *
     * @var File|null
     */
    private $bannerFile;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="category")
     */
    private $items;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $viewHomePage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $viewInMenu;

    /**
     * @ORM\ManyToMany(targetEntity=Tags::class, inversedBy="categories")
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="bannerCategories")
     */
    private $courseBanner;


    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getName');
    }

    public function setBannerFile($bannerFile = null): void
    {
        $this->bannerFile = $bannerFile;
        if (null !== $bannerFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getBannerFile()
    {
        return $this->bannerFile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
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

    /**
     * @return Collection|Item[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCategory($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getCategory() === $this) {
                $item->setCategory(null);
            }
        }

        return $this;
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

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): self
    {
        $this->banner = $banner;

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

    public function getViewHomePage(): ?bool
    {
        return $this->viewHomePage;
    }

    public function setViewHomePage(?bool $viewHomePage): self
    {
        $this->viewHomePage = $viewHomePage;

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


    public function getViewInMenu(): ?bool
    {
        return $this->viewInMenu;
    }

    /**
     * @param mixed $viewInMenu
     */
    public function setViewInMenu(bool $viewInMenu): void
    {
        $this->viewInMenu = $viewInMenu;
    }


    public function getCourseBanner(): ?Course
    {
        return $this->courseBanner;
    }

    /**
     * @param mixed $courseBanner
     */
    public function setCourseBanner(?Course $courseBanner): void
    {
        $this->courseBanner = $courseBanner;
    }
}
