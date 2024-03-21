<?php

namespace App\Repository;

use App\Entity\Sortie;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findSortiesFiltrees($filters, $userID)
    {
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.site', 'site')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e');


        if (!empty($filters['siteSelectionne'])) {
            $query->andWhere('site.id = :site')
                ->setParameter('site', $filters['siteSelectionne']);
        }

        if (!empty($filters['dateDebut'])) {
            $query->andWhere('s.dateHeuredebut >= :dateDebut')
                ->setParameter('dateDebut', $filters['dateDebut']);
        }

        if (!empty($filters['dateFin'])) {
            $query->andWhere('s.dateLimiteInscription <= :dateFin')
                ->setParameter('dateFin', $filters['dateFin']);
        }

        if (!empty($filters['motCle'])) {
            $query->andWhere('s.nom LIKE :motCle')
                ->setParameter('motCle', '%' . $filters['motCle'] . '%');
        }

        if (!empty($filters['organisateur'])) {
            $query->andWhere('o.id = :organisateur')
                ->setParameter('organisateur', $userID);
        } else {
            $query->andWhere($query->expr()->orX(
                $query->expr()->eq('o.id', ':organisateur'),
                $query->expr()->eq('p.id', ':organisateur')
            ))->setParameter('organisateur', $userID);
        }

        if (!empty($filters['inscrit'])) {
            $query->andWhere(':userId MEMBER OF s.participants')
                ->setParameter('userId', $userID);
        }

        if (!empty($filters['pasInscrit'])) {
            $query->andWhere('p.id IS NULL');
        }

        if (!empty($filters['passees'])) {
            $query->andWhere('s.dateHeuredebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $query->getQuery()->getResult();
    }

}