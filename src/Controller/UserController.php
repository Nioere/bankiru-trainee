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
        // Получаем репозиторий User через EntityManager
        $userRepository = $this->entityManager->getRepository(User::class);

        // Получаем параметры запроса и преобразуем их в целые числа
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 10);
        $sort = $request->query->get('sort', 'id');

        // Вызываем метод findAllWithPetIds, который должен быть определён в UserRepository
        $usersWithPetIds = $userRepository->findAllWithPetIds($page, $perPage, $sort);

        // Формируем данные для ответа
        $responseData = [];
        foreach ($usersWithPetIds as $user) {
            $responseData[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'createdAt' => $user->getCreatedAt()->format(DateTimeInterface::ATOM),
                'updatedAt' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format(DateTimeInterface::ATOM) : null,
                'petIds' => $user->getPetIds(),
            ];
        }

        return new JsonResponse($responseData);
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

        return new Response(null, Response::HTTP_NO_CONTENT);
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
