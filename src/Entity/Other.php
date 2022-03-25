<?php

namespace App\Entity;

use App\Traits\EmptyField;
use App\Traits\Trans;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use App\Repository\OtherRepository;
use App\Validator as AcmeAssert;
/**
 * @ORM\Entity(repositoryClass=OtherRepository::class)
 * @AcmeAssert\TranslationLength()
 */
class Other extends Item implements TranslatableInterface
{
    use TranslatableTrait;
    use Trans;
    use EmptyField;

    /**
     * @ORM\ManyToOne(targetEntity=VideoItem::class, inversedBy="others")
     */
    private $videoItem;

    public function __construct()
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return $this->proxyCurrentLocaleTranslation('getTitle');
    }

    /**
     * @inheritDoc
     */
    public  function getTypeItem(): string
    {
        return self::OTHER;
    }

    public function getVideoItem(): ?VideoItem
    {
        return $this->videoItem;
    }

    public function setVideoItem(?VideoItem $videoItem): self
    {
        $this->videoItem = $videoItem;

        return $this;
    }
    /**
     * @inheritDoc
     */
    public function __clone()
    {

    }
}
