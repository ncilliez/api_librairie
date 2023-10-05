<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LivreRepository; // N'oubliez pas d'importer LivreRepository
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Livre;
use App\Repository\AuthorRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LivreController extends AbstractController
{
    #[Route('/api/livres', name: 'app_livre', methods: ['GET'])]
    public function getLivreList(LivreRepository $livreRepository, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $livreList = $livreRepository->findAllWithPagination($page, $limit);
        $jsonLivreList = $serializer->serialize($livreList, 'json', ['groups' => 'getLivres']);
        return new JsonResponse($jsonLivreList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/livres/{id}', name: 'detailLivre', methods: ['GET'])]
    public function getDetailLivre(Livre $livre, SerializerInterface $serializer): JsonResponse 
    {
        $jsonLivre = $serializer->serialize($livre, 'json', ['groups' => 'getLivres']);
        return new JsonResponse($jsonLivre, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/livres/{id}', name: 'deleteLivre', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un livre')]
    public function deleteLivre(Livre $livre, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($livre);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/livres', name:"createLivre", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un livre')]
    public function createLivre(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse 
    {
        $livre = $serializer->deserialize($request->getContent(), Livre::class, 'json');
        // On vérifie les erreurs
        $errors = $validator->validate($livre);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idAuteur. S'il n'est pas défini, alors on met -1 par défaut.
        $idAuthor = $content['idAuteur'] ?? -1;

        // On cherche l'auteur qui correspond et on l'assigne au livre. (nécéssite d'importer "AuthorRepository")
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $livre->setAuthor($authorRepository->find($idAuthor));

        $em->persist($livre);
        $em->flush();

        $jsonLivre = $serializer->serialize($livre, 'json', ['groups' => 'getLivres']);
        
        $location = $urlGenerator->generate('detailLivre', ['id' => $livre->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonLivre, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/livres/{id}', name:"updateLivre", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un livre')]
    public function updateLivre(Request $request, SerializerInterface $serializer, Livre $currentLivre, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse 
    {
        $updatedLivre = $serializer->deserialize($request->getContent(), 
                Livre::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentLivre]);
        $content = $request->toArray();
        $idAuthor = $content['idAuteur'] ?? -1;
        $updatedLivre->setAuthor($authorRepository->find($idAuthor));
        
        $em->persist($updatedLivre);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}

