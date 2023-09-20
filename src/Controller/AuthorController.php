<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AuthorRepository; // N'oubliez pas d'importer AuthorRepository
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;

class AuthorController extends AbstractController
{
    #[Route('/api/auteurs', name: 'app_author', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/auteurs/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getAuthor(Author $author, SerializerInterface $serializer): JsonResponse 
    {
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, ['accept' => 'application/json'], true);
    }

    #[Route('/api/auteurs/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(Author $author, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($author);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/auteurs', name:"createAuteur", methods: ['POST'])]
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {

        $auteur = $serializer->deserialize($request->getContent(), Author::class, 'json');

        $em->persist($auteur);
        $em->flush();

        $jsonAuteur = $serializer->serialize($auteur, 'json', ['groups' => 'getAuthor']);
        
        $location = $urlGenerator->generate('detailAuthor', ['id' => $auteur->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAuteur, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}

