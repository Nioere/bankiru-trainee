<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pet;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/pet')]
class PetController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'pet_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 10);
        $sort = $request->query->get('sort', null);
        $ids = $request->query->all('ids');

        $repository = $this->entityManager->getRepository(Pet::class);

        $query = $repository->createQueryBuilder('p');

        if ($sort) {
            $direction = 'ASC';
            if ('-' === substr($sort, 0, 1)) {
                $direction = 'DESC';
                $sort = substr($sort, 1);
            }
            $query->orderBy('p.' . $sort, $direction);
        }

        if (!empty($ids)) {
            $query->where('p.id IN (:ids)')->setParameter('ids', $ids);
        }

        $query->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $pets = $query->getQuery()->getResult();

        return $this->json($pets);
    }

    #[Route('/{id}', name: 'pet_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $pet = $this->entityManager->getRepository(Pet::class)->find($id);

        return $this->json($pet);
    }

    /**
     * @throws Exception
     */
    #[Route('', name: 'pet_add', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['userId'])) {
            return $this->json(['message' => 'Необходимо указать ID пользователя.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
        if (!$user) {
            return $this->json(['message' => 'Пользователь не найден.'], Response::HTTP_NOT_FOUND);
        }

        $pet = new Pet();
        $pet->setUserId($data['userId']);
        $pet->setName($data['name']);
        $pet->setDescription($data['description']);
        $pet->setCreatedAt(new DateTime());
        $pet->setUpdatedAt(new DateTime());

        $this->entityManager->persist($pet);
        $this->entityManager->flush();

        $petIds = $user->getPetIds();

        if (($key = array_search(0, $petIds)) !== false) {
            unset($petIds[$key]);
        }

        $petIds[] = $pet->getId();
        $user->setPetIds(array_values($petIds));
        $this->entityManager->flush();

        return $this->json($pet, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'pet_update', methods: ['POST'])]
    public function updateOrCreate(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pet = $this->entityManager->getRepository(Pet::class)->find($id);

        if (!$pet) {
            return $this->json(['message' => 'Питомца не существует'], Response::HTTP_BAD_REQUEST);
        } else {
            $oldUser = $this->entityManager->getRepository(User::class)->find($pet->getUserId());
            if ($oldUser) {
                $oldPetIds = $oldUser->getPetIds();
                $key = array_search($pet->getId(), $oldPetIds);
                if (false !== $key) {
                    unset($oldPetIds[$key]);
                }
                $oldUser->setPetIds($oldPetIds);
            }
        }

        $newUser = $this->entityManager->getRepository(User::class)->find($data['userId']);
        if (!$newUser) {
            return $this->json(['message' => 'Пользователя не существует'], Response::HTTP_BAD_REQUEST);
        }

        $pet->setUserId($data['userId']);
        $pet->setName($data['name']);
        $pet->setDescription($data['description']);
        $pet->setCreatedAt(new DateTime($data['createdAt']));
        $pet->setUpdatedAt(new DateTime($data['updatedAt']));

        $this->entityManager->persist($pet);
        $this->entityManager->flush();

        $newPetIds = $newUser->getPetIds();

        if (($key = array_search(0, $newPetIds)) !== false) {
            unset($newPetIds[$key]);
        }

        if (!in_array($pet->getId(), $newPetIds)) {
            $newPetIds[] = $pet->getId();
        }

        $newUser->setPetIds(array_values($newPetIds));
        $this->entityManager->flush();

        return $this->json($pet, Response::HTTP_CREATED);
    }


    #[Route('/{id}', name: 'pet_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $pet = $this->entityManager->getRepository(Pet::class)->find($id);

        if (!$pet) {
            return $this->json(['error' => 'Питомца не существует'], Response::HTTP_NOT_FOUND);
        }

        $userId = $pet->getUserId();
        if ($userId) {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if ($user) {
                $petIds = $user->getPetIds();
                $key = array_search($id, $petIds);
                if (false !== $key) {
                    unset($petIds[$key]);
                }
                $user->setPetIds($petIds);
                $this->entityManager->flush();
            }
        }

        $this->entityManager->remove($pet);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
