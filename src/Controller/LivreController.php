<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LivreRepository; // N'oubliez pas d'importer LivreRepository
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Livre;

class LivreController extends AbstractController
{
    #[Route('/api/livres', name: 'app_livre', methods: ['GET'])]
    public function getLivreList(LivreRepository $livreRepository, SerializerInterface $serializer): JsonResponse
    {
        $livreList = $livreRepository->findAll();
        $jsonLivreList = $serializer->serialize($livreList, 'json');
        return new JsonResponse($jsonLivreList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/livres/{id}', name: 'detailLivre', methods: ['GET'])]
    public function getDetailLivre(Livre $livre, SerializerInterface $serializer): JsonResponse 
    {
        $jsonLivre = $serializer->serialize($livre, 'json');
        return new JsonResponse($jsonLivre, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}

