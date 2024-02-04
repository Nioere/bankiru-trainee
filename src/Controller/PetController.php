<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Pet;
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
            if (substr($sort, 0, 1) === '-') {
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

        $pet = new Pet();
        $pet->setUserId($data['userId']);
        $pet->setName($data['name']);
        $pet->setDescription($data['description']);
        $pet->setCreatedAt(new DateTime($data['createdAt']));
        $pet->setUpdatedAt(new DateTime($data['updatedAt']));

        $this->entityManager->persist($pet);
        $this->entityManager->flush();

        return $this->json($pet, Response::HTTP_CREATED);
    }

    #[Route('/api/v1/pet/{id}', name: 'pet_update', methods: ['POST'])]
    public function updateOrCreate(Request $request, int $id, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pet = $entityManager->getRepository(Pet::class)->find($id);

        if (!$pet) {
            $pet = new Pet();
        }

        $pet->setUserId($data['userId']);
        $pet->setName($data['name']);
        $pet->setDescription($data['description']);
        $pet->setCreatedAt(new DateTime($data['createdAt']));
        $pet->setUpdatedAt(new DateTime($data['updatedAt']));

        $entityManager->persist($pet);
        $entityManager->flush();

        // Возвращаем JSON-ответ с помощью сериализатора
        $responseData = $serializer->serialize($pet, 'json');

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'pet_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $pet = $this->entityManager->getRepository(Pet::class)->find($id);

        $this->entityManager->remove($pet);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
