<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebinarTranslationRepository;
use App\Traits\Seo;
use App\Traits\ShortText;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity(repositoryClass=WebinarTranslationRepository::class)
 */
class WebinarTranslation implements TranslationInterface
{
    use TranslationTrait, Seo;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionTwo;

    public function getDescriptionTwo(): ?string
    {
        return $this->descriptionTwo;
    }

    public function setDescriptionTwo(?string $descriptionTwo): self
    {
        $this->descriptionTwo = $descriptionTwo;

        return $this;
    }

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(?string $short): self
    {
        $this->short = $short;

        return $this;
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
     * Copy translation.
     */
    public function getCopy(): self
    {
        $result = new static();

        $result->setShort($this->getShort());
        $result->setTitle($this->getTitle());
        $result->setContent($this->getContent());

        $result = $this->copySeo($this, $result);

        return $result;
    }
}
