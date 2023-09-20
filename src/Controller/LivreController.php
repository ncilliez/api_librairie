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

class LivreController extends AbstractController
{
    #[Route('/api/livres', name: 'app_livre', methods: ['GET'])]
    public function getLivreList(LivreRepository $livreRepository, SerializerInterface $serializer): JsonResponse
    {
        $livreList = $livreRepository->findAll();
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
    public function deleteLivre(Livre $livre, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($livre);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/livres', name:"createLivre", methods: ['POST'])]
    public function createLivre(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository): JsonResponse 
    {

        $livre = $serializer->deserialize($request->getContent(), Livre::class, 'json');

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idAuthor. S'il n'est pas défini, alors on met -1 par défaut.
        $idAuthor = $content['idAuteur'] ?? -1;

        // On cherche l'auteur qui correspond et on l'assigne au livre.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $livre->setAuthor($authorRepository->find($idAuthor));

        $em->persist($livre);
        $em->flush();

        $jsonLivre = $serializer->serialize($livre, 'json', ['groups' => 'getLivres']);
        
        $location = $urlGenerator->generate('detailLivre', ['id' => $livre->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonLivre, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}

