<?php

namespace App\Repository;

use PDO;
use App\Entity\Operations;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Operations>
 *
 * @method Operations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operations[]    findAll()
 * @method Operations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operations::class);
    }

    /**
     * @return Operations[] Returns an array of Operations objects
     */
    // Add a method to find operations by user ID
    public function findByUserId($userId): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.users', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }


    /*
    *
    * @author: Dylan Rohart
    *     Stats+ complexes pour graphiques
    *
    */

    /*
    * Récuperer le nombre de services commandé en tout temps, par type. Si la commande n'a pas été estimé par un employé, alors aucun status id n'est attribué à l'opération, 
    * Dans ce cas, on affiche quand même l'opération dans le graph mais avec le libellé "à estimer"
    */
    public function getOperationsData()
    {
        $qb = $this->createQueryBuilder('o')
            ->select('CASE WHEN o.services IS NULL THEN \'À estimer\' ELSE s.type END AS typeLabel, COUNT(o.id) AS operationsCount')
            ->leftJoin('o.services', 's')
            ->groupBy('typeLabel')
            ->orderBy('typeLabel', 'ASC');

        // Exécuter la requête et récupérer les résultats
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /*
    * Check et affiche le nombre d'opérations commandée de différence avec le mois précédent 
    */
    public function getMonthlyOperationsDifference()
    {
        $conn = $this->getEntityManager()->getConnection();

        // Simplification de la requête pour retourner directement la différence
        $sql = '
            SELECT 
                (SELECT COUNT(*) FROM operations WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())) -
                (SELECT COUNT(*) FROM operations WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH))
            AS difference
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $difference = $resultSet->fetchOne();

        return $difference;
    }


    /*
    * En utilisant la fonction précédente, on affiche l'équivalent en pourcentage
    */
    public function getMonthlyOperationsPercentageDifference()
    {
        // Récupération de la différence brute entre ce mois et le mois précédent
        $difference = $this->getMonthlyOperationsDifference();

        // Calcul du nombre total d'opérations du mois précédent pour servir de base au calcul du pourcentage
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT COUNT(*) FROM operations WHERE MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $lastMonthOperations = $resultSet->fetchOne();

        // Si le nombre d'opérations du mois précédent est 0, éviter une division par zéro
        if ($lastMonthOperations == 0) {
            return 0; // ou toute autre valeur représentant "pas de changement" ou "non applicable"
        }

        // Calculer la différence en pourcentage
        $percentageDifference = ($difference / $lastMonthOperations) * 100;

        // Formatage du résultat pour inclure le signe plus si nécessaire et limiter à 2 décimales
        $formattedPercentageDifference = sprintf("%0.2f%%", $percentageDifference);

        return $formattedPercentageDifference;
    }

    /*
    * Récuperer le nombre de services commandé, par type. Si la commande n'a pas été estimé par un employé, alors aucun status id n'est attribué à l'opération, 
    * PAR MOIS
    */
    public function getMonthlyOperationsData()
    {
        $qb = $this->createQueryBuilder('o')
            ->select('CASE WHEN o.services IS NULL THEN \'À estimer\' ELSE s.type END AS typeLabel, COUNT(o.id) AS operationsCount')
            ->leftJoin('o.services', 's')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', new \DateTime('first day of this month 00:00:00'))
            ->setParameter('end', new \DateTime('last day of this month 23:59:59'))
            ->groupBy('typeLabel')
            ->orderBy('typeLabel', 'ASC');

        // Exécutez la requête et récupérez les résultats
        $result = $qb->getQuery()->getResult();

        return $result;
    }


    /*
    *
    * @author : Mathilde Brx
    *
    */


    //pour calculer ce que rapporte un employé par mois (pour les stats)
    public function calculateRevenueByEmployeeAndMonth()
    {
        $bdd = new PDO('mysql:host=localhost;dbname=filrouge;charset=utf8mb4', 'root', '');
        $stmt = $bdd->prepare("SELECT 
        u.id AS id_employe,
        u.lastname AS nom_employe,
        u.firstname AS prenom_employe,
        months.mois AS mois,
        COALESCE(SUM(o.price), 0) AS argent_rapporte
            FROM 
                users u
            CROSS JOIN 
                (SELECT 1 AS mois UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) AS months
            LEFT JOIN 
                operations_users ou ON u.id = ou.users_id
            LEFT JOIN 
                operations o ON ou.operations_id = o.id AND MONTH(o.created_at) = months.mois
            WHERE
                JSON_CONTAINS(u.roles, '\"ROLE_USER\"') = 0
            GROUP BY 
                u.id, months.mois
            ORDER BY 
                u.id, months.mois;");

        $stmt->execute();
        $sql = $stmt->fetchAll();

        return $sql;
    }

    /*
    *
    * @author : Dylan Rohart
    *   "Simple" stats
    *
    */

    // Récuperer le nombre de commandes en cours
    public function ongoingOrder()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(*) FROM operations WHERE status = "En cours";
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $ongoingOrder = $resultSet->fetchOne();

        return $ongoingOrder;
    }
    // Récuperer le nombre de commandes en cours
    public function finishedOrder()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(*) FROM operations WHERE status = "Terminée";
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $finishedOrder = $resultSet->fetchOne();

        return $finishedOrder;
    }

    // Additionner tous les prix des opérations pour afficher un chiffre d'affaire
    public function calculateAllRevenues()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT SUM(`price`) FROM `operations` 
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $totalCA = $resultSet->fetchOne();

        return $totalCA;
    }
    // Retourner le nom du type d'opération le plus demandé
    public function mostOrdered()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT type 
        FROM services
        WHERE id = ( SELECT services_id
        FROM operations
        GROUP BY services_id
        ORDER BY COUNT(services_id) DESC
        LIMIT 1
        )
        ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $mostOrdered = $resultSet->fetchOne();

        return $mostOrdered;
    }

    // Calculer le taux d'operations terminée du mois par rapport au mois précédent
    public function findEndedOperationDifferences()
    {
        $qb = $this->createQueryBuilder('o');

        // Définir les formats de date pour ce mois et le mois précédent
        $ceMois = new \DateTime('first day of this month');
        $moisPrecedent = (new \DateTime('first day of this month'))->sub(new \DateInterval('P1M'));

        // Créer une expression conditionnelle pour déterminer la période
        $whenCeMois = $qb->expr()->eq($qb->expr()->substring('o.finishedAt', 1, 7), ':ceMois');
        $whenMoisPrecedent = $qb->expr()->eq($qb->expr()->substring('o.finishedAt', 1, 7), ':moisPrecedent');

        $qb->select('SUM(CASE WHEN ' . $whenCeMois . ' THEN 1 ELSE 0 END) AS OperationsCeMois')
            ->addSelect('SUM(CASE WHEN ' . $whenMoisPrecedent . ' THEN 1 ELSE 0 END) AS OperationsMoisPrecedent')
            ->where('o.status = :status')
            ->andWhere($qb->expr()->orX($whenCeMois, $whenMoisPrecedent))
            ->setParameter('ceMois', $ceMois->format('Y-m'))
            ->setParameter('moisPrecedent', $moisPrecedent->format('Y-m'))
            ->setParameter('status', 'Terminée')
            ->groupBy('o.status'); // Groupe par status pour s'assurer d'avoir des résultats cohérents

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            // Gérer le cas où aucun résultat n'est retourné par la requête
            // Par exemple, en retournant des valeurs par défaut ou en gérant une erreur
            return [
                'Difference' => 0,
                'PourcentageAugmentation' => 0,
            ];
        }

        // Calculer la différence et le pourcentage d'augmentation
        $difference = $result['OperationsCeMois'] - $result['OperationsMoisPrecedent'];
        $pourcentageAugmentation = $result['OperationsMoisPrecedent'] > 0
            ? round(($difference * 100.0 / $result['OperationsMoisPrecedent']), 2)
            : 0;


        return [
            'Difference' => $difference,
            'PourcentageAugmentation' => $pourcentageAugmentation,
        ];
    }

    // Recuperer le total de revenus de cette année et le comparer à l'année précédente 
    public function compareAnnualRevenues()
    {
        $qb = $this->createQueryBuilder('o');

        // Définir les années actuelle et précédente
        $anneeActuelle = new \DateTime('first day of January this year');
        $anneePrecedente = (new \DateTime('first day of January last year'));

        // Calculer les revenus pour l'année actuelle
        $qbActuel = clone $qb;
        $revenusActuels = $qbActuel->select('SUM(o.price) AS RevenusActuels')
            ->where('o.createdAt >= :debutAnneeActuelle')
            ->andWhere('o.createdAt < :debutAnneeSuivante')
            ->setParameter('debutAnneeActuelle', $anneeActuelle)
            ->setParameter('debutAnneeSuivante', $anneeActuelle->modify('+1 year'))
            ->getQuery()
            ->getSingleScalarResult();

        // Réinitialiser le QueryBuilder pour l'année précédente
        $qbPrecedent = clone $qb;
        $revenusPrecedents = $qbPrecedent->select('SUM(o.price) AS RevenusPrecedents')
            ->where('o.createdAt >= :debutAnneePrecedente')
            ->andWhere('o.createdAt < :debutAnneeActuelle')
            ->setParameter('debutAnneePrecedente', $anneePrecedente)
            ->setParameter('debutAnneeActuelle', $anneeActuelle) // Déjà défini mais nécessaire pour la clarté
            ->getQuery()
            ->getSingleScalarResult();

        // Calculer la différence et le pourcentage d'augmentation
        $difference = $revenusPrecedents - $revenusActuels;
        if ($revenusPrecedents > 0) {
            $pourcentageAugmentation = ($difference * 100.0 / $revenusPrecedents);
        } elseif ($revenusActuels > 0) {
            // Les revenus de l'année précédente sont 0, mais pas ceux de l'année actuelle
            $pourcentageAugmentation = "100"; // Ou toute autre représentation significative
        } else {
            // Aucun revenu ni cette année ni l'année précédente
            $pourcentageAugmentation = 0; // Ou considérez ceci comme une situation spéciale
        }

        return [
            'Difference' => $difference,
            'PourcentageAugmentation' => $pourcentageAugmentation,
        ];
    }



    //  calculer la durée moyenne de clôture d'une opération en journée de 7h
    public function getAverageCompletionHoursByServiceType()
    {
        // Obtiens le gestionnaire d'entités
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT 
            Services.Type,
            ROUND(
                AVG(
                    TIMESTAMPDIFF(HOUR, Operations.created_at, Operations.finished_at) / 24
                ), 0
            ) AS AverageCompletionHours
            FROM 
                Operations
            JOIN 
                Services ON Operations.services_id = Services.id
            WHERE 
                Operations.status = 'Terminée'
            GROUP BY 
                Services.Type
            ORDER BY 
                `Type` DESC;
        ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $averageCompletionTimes = $resultSet->fetchAllAssociative();

        return $averageCompletionTimes;
    }


    /*
    *
    * @author: Amélie 
    *     Stats pour les clients
    *
    */

    // Récupérer le nombre d'opérations en cours pour le client connecté
    public function onGoingOperationsClient($userId): int
    {
        $operations = $this->findByUserId($userId);
        $onGoingOperationClient = [];
        foreach ($operations as $operation) {
            if ($operation->getStatus() === 'En cours' || $operation->getStatus() === 'Disponible') {
                $onGoingOperationClient[] = $operation;
            }
        }
        $ongoingOperationsClient = count($onGoingOperationClient);
        return  $ongoingOperationsClient;
    }


    // Récupérer le nombre d'opérations en cours pour le client connecté
    public function getNumberPerService($userId, string $status1, string $status2): array
    {
        $operations = $this->findByUserId($userId);
        $smallService = 0;
        $mediumService = 0;
        $bigService = 0;
        $customService = 0;
        foreach ($operations as $operation) {
            if ($operation->getStatus() === $status1 || $operation->getStatus() === $status2) {
                $service = $operation->getServices();
                if ($service !== null) { // Vérifiez si le service est défini
                    $type = $service->getType();
                    if ($type === 'Petite') {
                        $smallService++;
                    } elseif ($type === 'Moyenne') {
                        $mediumService++;
                    } elseif ($type === 'Grande') {
                        $bigService++;
                    } elseif ($type === 'Custom') {
                        $customService++;
                    }
                }
            }
        }
        $numberPerService = [
            'Petite' => $smallService,
            'Moyenne' =>  $mediumService,
            'Grande' => $bigService,
            'Custom' => $customService
        ];
        return $numberPerService;
    }

    // Récupérer le nombre d'opérations en cours pour le client connecté
    public function mostWantedService($userId): string
    {
        $numberPerService = $this->getNumberPerService($userId, "En cours", "Disponible");
        $mostWantedService = array_search(max($numberPerService), $numberPerService);

        return $mostWantedService;
    }

    // Récupérer le nombre d'opérations en cours pour le client connecté
    public function finishedOperationsClient($userId): int
    {
        $operations = $this->findByUserId($userId);
        $finishedOperationsClient = [];
        foreach ($operations as $operation) {
            if ($operation->getStatus() === 'Terminée') {
                $finishedOperationsClient[] = $operation;
            }
        }
        $finishedOperationsClient = count($finishedOperationsClient);
        return  $finishedOperationsClient;
    }
}
