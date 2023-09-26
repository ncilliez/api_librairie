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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        // Supprimez les livres de l'auteur en cascade
        foreach ($author->getLivres() as $livre) {
            $em->remove($livre);
        }

        $em->remove($author);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/auteurs', name:"createAuteur", methods: ['POST'])]
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse 
    {
        $auteur = $serializer->deserialize($request->getContent(), Author::class, 'json');
        // On vérifie les erreurs
        $errors = $validator->validate($auteur);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($auteur);
        $em->flush();

        $jsonAuteur = $serializer->serialize($auteur, 'json', ['groups' => 'getAuthor']);
        
        $location = $urlGenerator->generate('detailAuthor', ['id' => $auteur->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAuteur, Response::HTTP_CREATED, ["Location" => $location], true);
   }

   #[Route('/api/auteurs/{id}', name:"updateAuteur", methods:['PUT'])]
    public function updateAuteur(Request $request, SerializerInterface $serializer, Author $currentAuteur, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse 
    {
        $updatedAuteur = $serializer->deserialize($request->getContent(), 
                Author::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuteur]);
        
        $em->persist($updatedAuteur);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}

