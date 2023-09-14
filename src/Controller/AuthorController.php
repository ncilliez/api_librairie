<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository; // N'oubliez pas d'importer AuthorRepository
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Author;

class AuthorController extends AbstractController
{
    #[Route('/api/auteurs', name: 'app_author', methods: ['GET'])]
    public function getAllAuthor(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/auteurs/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getAuthor(Author $author, SerializerInterface $serializer): JsonResponse 
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
