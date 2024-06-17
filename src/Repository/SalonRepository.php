<?php

namespace App\Repository;

use App\Entity\Salon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salon>
 *
 * @method Salon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salon[]    findAll()
 * @method Salon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salon::class);
    }
}
