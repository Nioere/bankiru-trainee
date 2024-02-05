<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/user')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'user_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $repository = $this->entityManager->getRepository(User::class);

        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 10);
        $sort = $request->query->get('sort', null);
        $ids = $request->query->all('ids');

        $query = $repository->createQueryBuilder('u');

        if ($sort) {
            $direction = 'ASC';
            if (str_starts_with($sort, '-')) {
                $direction = 'DESC';
                $sort = substr($sort, 1);
            }
            $query->orderBy('u.' . $sort, $direction);
        }

        if (!empty($ids)) {
            $query->where('u.id IN (:ids)')->setParameter('ids', $ids);
        }

        $query->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $users = $query->getQuery()->getResult();

        $responseData = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'createdAt' => $user->getCreatedAt()->format(DateTimeInterface::ATOM),
                'updatedAt' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format(DateTimeInterface::ATOM) : null,
                'petIds' => $user->getPetIds(),
            ];
        }, $users);

        return $this->json($responseData);
    }


    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->json($user);
    }

    #[Route('', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setCreatedAt(new DateTime());
        $user->setUpdatedAt(new DateTime());
        $user->setPetIds($data['petIds']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'user_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Пользователь не найден');
        }

        $data = json_decode($request->getContent(), true);

        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setUpdatedAt(new DateTime());
        $user->setPetIds($data['petIds']);

        $newPetIds = $data['petIds'] ?? [];

        $currentPets = $this->entityManager->getRepository(Pet::class)->findBy(['userId' => $user->getId()]);

        foreach ($currentPets as $pet) {
            if (!in_array($pet->getId(), $newPetIds)) {
                $pet->setUserId((int) null);
                $this->entityManager->persist($pet);
            }
        }

        foreach ($newPetIds as $petId) {
            $pet = $this->entityManager->getRepository(Pet::class)->find($petId);
            if ($pet && $pet->getUserId() !== $user->getId()) {
                $pet->setUserId($user->getId());
                $this->entityManager->persist($pet);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'createdAt' => $user->getCreatedAt()->format(DateTime::ISO8601),
            'updatedAt' => $user->getUpdatedAt()->format(DateTime::ISO8601),
            'petIds' => $user->getPetIds(),
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
