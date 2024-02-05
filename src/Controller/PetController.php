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
use Symfony\Component\Serializer\SerializerInterface;

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

        $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
        if ($user) {
            $petIds = $user->getPetIds();
            if (!in_array($pet->getId(), $petIds)) {
                $petIds[] = $pet->getId();
                $user->setPetIds($petIds);
                $this->entityManager->flush();
            }
        }

        return $this->json($pet, Response::HTTP_CREATED);
    }

    #[Route('/api/v1/pet/{id}', name: 'pet_update', methods: ['POST'])]
    public function updateOrCreate(Request $request, int $id, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pet = $entityManager->getRepository(Pet::class)->find($id);

        if (!$pet) {
            $pet = new Pet();
        } else {
            $oldUser = $entityManager->getRepository(User::class)->find($pet->getUserId());
            if ($oldUser) {
                $oldPetIds = $oldUser->getPetIds();
                $key = array_search($pet->getId(), $oldPetIds);
                if (false !== $key) {
                    unset($oldPetIds[$key]);
                }
                $oldUser->setPetIds($oldPetIds);
            }
        }

        $pet->setUserId($data['userId']);
        $pet->setName($data['name']);
        $pet->setDescription($data['description']);
        $pet->setCreatedAt(new DateTime($data['createdAt']));
        $pet->setUpdatedAt(new DateTime($data['updatedAt']));

        $entityManager->persist($pet);
        $entityManager->flush();

        $newUser = $entityManager->getRepository(User::class)->find($data['userId']);
        $newPetIds = $newUser->getPetIds();
        $newPetIds[] = $pet->getId();
        $newUser->setPetIds($newPetIds);
        $entityManager->flush();

        $responseData = $serializer->serialize($pet, 'json');

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'pet_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $pet = $this->entityManager->getRepository(Pet::class)->find($id);

        $user = $this->entityManager->getRepository(User::class)->find($pet->getUserId());
        $petIds = $user->getPetIds();
        $key = array_search($id, $petIds);
        if (false !== $key) {
            unset($petIds[$key]);
        }
        $user->setPetIds($petIds);
        $this->entityManager->flush();

        $this->entityManager->remove($pet);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
