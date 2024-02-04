<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findAllWithPetIds(int $page = 1, int $perPage = 10, string $sort = 'id'): array
    {
        $offset = ($page - 1) * $perPage;

        $sortDirection = 'ASC';
        if (0 === strpos($sort, '-')) {
            $sortDirection = 'DESC';
            $sort = substr($sort, 1);
        }

        $query = $this->createQueryBuilder('u')
            ->orderBy('u.' . $sort, $sortDirection)
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->getQuery();

        $users = $query->getResult();

        foreach ($users as $user) {
            $petIds = $this->getEntityManager()->createQuery(
                'SELECT p.id
                FROM App\Entity\Pet p
                WHERE p.userId = :userId',
            )->setParameter('userId', $user->getId())
                ->getResult();

            $user->setPetIds(array_column($petIds, 'id'));
        }

        return $users;
    }
}
