<?php
declare(strict_types=1);

namespace App\Entity\Market;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\CommodityKitRepository;
use App\Entity\User;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass=CommodityKitRepository::class)
 * @ORM\Table(name="market_commodity_kit")
 */
class CommodityKit extends Commodity
{
    public const REQUIRED_USER_ROLES = [
        User::ROLE_SALESMAN,
        User::ROLE_SERVICE_PROVIDER,
    ];
    /**
     * @ORM\ManyToMany(targetEntity=Commodity::class)
     */
    private Collection $commodities;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isApproved = null;

    public function __construct()
    {
        parent::__construct();
        $this->commodities = new ArrayCollection();
    }

    /**
     * @return Collection|Commodity[]
     */
    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    public function addCommodity(Commodity $commodity): self
    {
        if (!$this->commodities->contains($commodity)) {
            $this->commodities[] = $commodity;
        }

        return $this;
    }

    public function removeCommodity(Commodity $commodity): self
    {
        $this->commodities->removeElement($commodity);

        return $this;
    }

    public function getIsApproved(): bool
    {
        return $this->isApproved ?? false;
    }

    public function setIsApproved(?bool $isApproved): self
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getCommodityType(): string
    {
        return Commodity::TYPE_KIT;
    }

    /**
     * @param bool $snapshot
     *
     * @return User[]
     */
    public function getCoAuthors(bool $snapshot): array
    {
        $commodities = $snapshot
            ? $this->getCommodities()->getSnapshot()
            : $this->getCommodities();

        return $this->findCoAuthorsAmongCommodities($commodities);
    }

    /**
     * @param PersistentCollection $commodities
     * @return array
     */
    private function findCoAuthorsAmongCommodities($commodities): array
    {
        $coAuthors = [];
        foreach ($commodities as $commodity) {
            if (!in_array($commodity->getUser(), $coAuthors)
                && $commodity->getUser() != $this->getUser()
            ) {
                $coAuthors[] = $commodity->getUser();
            }
        }
        return $coAuthors;
    }
}
