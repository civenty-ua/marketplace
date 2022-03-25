<?php

namespace App\Entity;

use App\Repository\ExpertRepository;
use App\Traits\EmptyField;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AcmeAssert;

/**
 * @ORM\Entity(repositoryClass=ExpertRepository::class)
 * @AcmeAssert\TranslationLength()
 */
class Expert implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(protocols = {"http", "https", "ftp"})
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity=Item::class, mappedBy="experts")
     */
    private $items;

    /**
     * @ORM\ManyToMany(targetEntity=ExpertType::class, inversedBy="items")
     */
    private $expertTypes;

    /**
     * @ORM\ManyToMany(targetEntity=Tags::class, inversedBy="experts")
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(protocols = {"http", "https"})
     */
    private $facebook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     */
    private $linkedin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *  @Assert\Url()
     */
    private $twitter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *  @Assert\Url()
     */
    private $youtube;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(protocols = {"http", "https", "ftp"})
     */
    private $telegram;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(protocols = {"http", "https", "ftp"})
     */
    private $instagram;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->expertTypes = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getName');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): self
    {
        $this->site = $site;

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
            $item->addExpert($this);
        }

        return $this;
    }

    public function removeItem(Item $item): self
    {
        if ($this->items->removeElement($item)) {
            $item->removeExpert($this);
        }

        return $this;
    }

    /**
     * @return Collection|\App\Entity\ExpertType[]
     */
    public function getExpertTypes(): Collection
    {
        return $this->expertTypes;
    }

    public function addExpertType(ExpertType $expertType): self
    {
        if (!$this->expertTypes->contains($expertType)) {
            $this->expertTypes[] = $expertType;
        }

        return $this;
    }

    public function removeExpertType(ExpertType $expertType): self
    {
        $this->expertTypes->removeElement($expertType);

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

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): self
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): self
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function setTelegram(?string $telegram): self
    {
        $this->telegram = $telegram;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }


    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
