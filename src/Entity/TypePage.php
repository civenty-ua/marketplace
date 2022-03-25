<?php

namespace App\Entity;

use App\Repository\TypePageRepository;
use App\Traits\Trans;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass=TypePageRepository::class)
 */
class TypePage implements TranslatableInterface, TimestampableInterface
{
    use TranslatableTrait;
    use TimestampableTrait;
    use Trans;

    const TYPE_BUSINESS_TOOLS = 'business_tools';
    const TYPE_NEWS = 'news';
    const TYPE_SUCCESS_STORIES = 'success_stories';
    const TYPE_ECO_ARTICLES = 'eco_articles';
    const TYPE_ARTICLE = 'article';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="typePage", orphanRemoval=true)
     */
    private $articles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=Page::class, mappedBy="typePage", orphanRemoval=true)
     */
    private $pages;


    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->pages = new ArrayCollection();
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
            $article->setTypePage($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getTypePage() === $this) {
                $article->setTypePage(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Page[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setTypePage($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getTypePage() === $this) {
                $page->setTypePage(null);
            }
        }

        return $this;
    }
}
