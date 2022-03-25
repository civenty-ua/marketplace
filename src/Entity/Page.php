<?php

namespace App\Entity;

use App\Repository\PageRepository;
use App\Traits\ShortText;
use App\Traits\EmptyField;
use App\Traits\Trans;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Validator as AcmeAssert;
/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @AcmeAssert\TranslationLength()
 */
class Page implements TranslatableInterface, TimestampableInterface
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
     * @ORM\Column(type="string", length=255)
     */
    private $alias;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageName;

    /**
     * @ORM\ManyToOne(targetEntity=TypePage::class, inversedBy="pages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $typePage;


    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getTypePage(): ?TypePage
    {
        return $this->typePage;
    }

    public function setTypePage(?TypePage $typePage): self
    {
        $this->typePage = $typePage;

        return $this;
    }
}
