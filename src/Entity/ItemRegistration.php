<?php

namespace App\Entity;

use App\Repository\ItemRegistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity(repositoryClass=ItemRegistrationRepository::class)
 */
class ItemRegistration implements TimestampableInterface
{
    use TimestampableTrait;

    private const COURSE = 'course';
    private const WEBINAR = 'webinar';
    private const OCCURRENCE = 'occurrence';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="itemRegistrations")
     */
    private $userId;

    /**
     * @ManyToOne(targetEntity="Item")
     */
    private $itemId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemType;

    private $types = [
        self::COURSE => 'Курс',
        self::WEBINAR => 'Вебінар',
        self::OCCURRENCE => 'Захід'
    ];

    /**
     * TODO: find normal solution!
     *
     * This is for easy admin!
     * If entity has no getter/public property - "setVirtual" + "formatValue" does not work!
     * Entities with "Trans" trait already have "__get" magic method.
     */

    public function __get($name)
    {
        return null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getItemId(): ?Item
    {
        return $this->itemId;
    }

    public function setItemId(Item $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    public function getItemType(): ?string
    {
        return $this->types[$this->getItemId()->getTypeItem()];
    }

    public function setItemType(): self
    {

        $this->itemType = $this->getItemId()->getTypeItem();

        return $this;
    }
}
