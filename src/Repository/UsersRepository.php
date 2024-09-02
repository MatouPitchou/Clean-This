<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<Users>
 *
 * @implements PasswordUpgraderInterface<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function add(Users $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush)
        {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

       /**
        * @return Users[] Returns an array of Users objects
        * @todo La fonction fonctionne désormais, il faudrait l'ajouter à l'indexQueryBuilder de CustomerCrudController et UsersCrudController
        */
    public function findByRoles(array $roles): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $orX = $queryBuilder->expr()->orX();
        $index = 0;

        foreach ($roles as $role) {
            $roleParam = 'role'.$index;
            $orX->add($queryBuilder->expr()->like('u.roles', ':' . $roleParam));
            $queryBuilder->setParameter($roleParam, '%"'.$role.'"%');
            $index++;
        }

        $queryBuilder->andWhere($orX);

        return $queryBuilder;
    }

    public function findUserNameByOperationAndRole(int $operationId, string $role): ?array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.firstname', 'u.lastname') // Sélectionnez à la fois le prénom et le nom de famille
            ->innerJoin('u.operations', 'o')
            ->where('o.id = :operationId')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('operationId', $operationId)
            ->setParameter('role', '%"'.$role.'"%')
            ->getQuery();

        $result = $qb->getOneOrNullResult();

        return $result ? $result : null;
    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


}
