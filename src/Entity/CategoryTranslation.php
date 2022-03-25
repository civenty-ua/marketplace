<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryTranslationRepository;
use App\Traits\Seo;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity(repositoryClass=CategoryTranslationRepository::class)
 *
 */
class CategoryTranslation implements TranslationInterface
{
    use TranslationTrait;
    use Seo;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $learningTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $articleTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bottomContent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArticleTitle()
    {
        return $this->articleTitle;
    }

    /**
     * @param mixed $articleTitle
     */
    public function setArticleTitle($articleTitle): void
    {
        $this->articleTitle = $articleTitle;
    }

    /**
     * @return mixed
     */
    public function getLearningTitle()
    {
        return $this->learningTitle;
    }

    /**
     * @param mixed $learningTitle
     */
    public function setLearningTitle($learningTitle): void
    {
        $this->learningTitle = $learningTitle;
    }

    public function getBottomContent(): ?string
    {
        return $this->bottomContent;
    }

    public function setBottomContent(?string $bottomContent): self
    {
        $this->bottomContent = $bottomContent;

        return $this;
    }
}
