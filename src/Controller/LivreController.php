<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LivreRepository; // N'oubliez pas d'importer LivreRepository
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Livre;

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
}

