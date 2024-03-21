<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etat>
 *
 * @method Etat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etat[]    findAll()
 * @method Etat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etat::class);
    }

    public function updateEtat(Sortie $sortie): void
    {
        $dateDebutMoinsUneSemaine = (clone $sortie->getDateHeureDebut())->modify('-1 week');
        $dateFinEvent = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' hours');
        $datePlusUnMois = (clone $sortie->getDateHeureDebut())->modify('+1 month');
        $now = new \DateTimeImmutable();
        $etatActuel = $sortie->getEtat();

        if ($etatActuel && $sortie->getDateDebutInscription() > $now && $etatActuel->getId() === 7) {
            // Ne rien faire car l'état "Créée" est déjà défini
        }

        if ($etatActuel && $dateDebutMoinsUneSemaine <= $now && $etatActuel->getId() === 7) {
            $sortie->setEtat($this->find(2)); // 'Ouverte' est '2'
        }

        if ($etatActuel && $sortie->getDateLimiteInscription() <= $now && $etatActuel->getId() === 2) {
            $sortie->setEtat($this->find(3)); // 'Clôturée' est '3'
        }

        if ($etatActuel && $sortie->getDateHeureDebut() <= $now && $etatActuel->getId() === 3) {
            $sortie->setEtat($this->find(1)); // 'En cours' est '1'
        }

        if ($etatActuel && $dateFinEvent <= $now && $etatActuel->getId() === 1) {
            $sortie->setEtat($this->find(3)); // 'Terminée' est '3'
        }

        if ($etatActuel && $datePlusUnMois <= $now && $etatActuel->getId() === 3) {
            // Supposons que 'Archivée' est '7'
            $sortie->setEtat($this->find(7));
        }
    }
}