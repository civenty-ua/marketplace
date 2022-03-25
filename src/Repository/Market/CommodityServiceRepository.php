<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\{
    Market\Commodity,
    Market\CommodityService,
};
/**
 * @method CommodityService|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityService|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityService[]    findAll()
 * @method CommodityService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityServiceRepository extends ServiceEntityRepository
{
    use ProductsAndServicesRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityService::class);
    }
    /**
     * @inheritDoc
     */
    protected function getCommodityType(): string
    {
        return Commodity::TYPE_SERVICE;
    }
}
