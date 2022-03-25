<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Traits\Trans;
use App\Repository\RegionRepository;
/**
 * @ORM\Entity(repositoryClass=RegionRepository::class)
 */
class Region implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\OneToMany(targetEntity=Partner::class, mappedBy="region")
     */
    private ?Collection $partners;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="region")
     */
    private ?Collection $articles;

    /**
     * @ORM\OneToMany(targetEntity=News::class, mappedBy="region")
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $sort;

    /**
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="region")
     */
    private Collection $contacts;

    /**
     * @ORM\OneToMany(targetEntity=District::class, mappedBy="region", orphanRemoval=true)
     */
    private Collection $districts;

    public function __construct()
    {
        $this->partners = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->districts = new ArrayCollection();
        $this->news     = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getName');
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $partner->setRegion($this);
        }

        return $this;
    }

    public function removePartner(Partner $partner): self
    {
        if ($this->partners->removeElement($partner)) {
            if ($partner->getRegion() === $this) {
                $partner->setRegion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setRegion($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getRegion() === $this) {
                $article->setRegion(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setRegion($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getRegion() === $this) {
                $news->setRegion(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSort(): ?int
    {
        return $this->sort;
    }

    /**
     * @param int|null $sort
     */
    public function setSort(?int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setRegion($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            if ($contact->getRegion() === $this) {
                $contact->setRegion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|District[]
     */
    public function getDistricts(): Collection
    {
        return $this->districts;
    }

    public function addDistrict(District $district): self
    {
        if (!$this->districts->contains($district)) {
            $this->districts[] = $district;
            $district->setRegion($this);
        }

        return $this;
    }

    public function removeDistrict(District $district): self
    {
        if ($this->districts->removeElement($district)) {
            if ($district->getRegion() === $this) {
                $district->setRegion(null);
            }
        }

        return $this;
    }
}
