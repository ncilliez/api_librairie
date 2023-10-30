<?php

namespace App\Controller;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/document/{filename}', name: 'show_media')]
    public function show($filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/media/' . $filename;

        // Vérifiez si le fichier existe
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('L\'image n\'existe pas.');
        }

        // Retourne la réponse avec l'image
        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => 'image/jpeg', // Remplacez "image/jpeg" par le type MIME approprié de votre image
        ]);
    }

    #[Route('/api/document/{id}', name: 'detailDocument', methods: ['GET'])]
    public function getDetailLivre(Document $document, SerializerInterface $serializer): JsonResponse 
    {
        $jsonDocument = $serializer->serialize($document, 'json', ['groups' => 'getLivres']);
        return new JsonResponse($jsonDocument, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/documents', name: 'createDocument', methods: ['POST'])]
    public function createDocument(Request $request, SerializerInterface $serializer, SluggerInterface $slugger, MimeTypesInterface $mimeTypes, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        // Vérifiez si un fichier a été envoyé dans la requête
        if ($request->files->has('file')) {
            $file = $request->files->get('file');

            // Générez un nom de fichier unique pour éviter les collisions
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $originalFilename.'-'.uniqid().'.'.$file->guessExtension();

            // Déplacez le fichier vers le répertoire où vous souhaitez le stocker
            try {
                $file->move(
                    $this->getParameter('document_directory'), // Répertoire de destination (vous devez le configurer dans config/services.yaml)
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer les erreurs d'upload
                return new JsonResponse(['error' => 'Erreur lors de l\'upload du fichier.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Mettez à jour l'entité Document avec le chemin du fichier
            $document = new Document();
            $document->setPathDocument("http://localhost:8000/api/document/".$newFilename);

            // Enregistrez l'entité Document dans la base de données
            $entityManager = $this->entityManager;
            $entityManager->persist($document);
            $entityManager->flush();

            $jsonLivre = $serializer->serialize($document, 'json', ['groups' => 'getLivres']);
        
            $location = $urlGenerator->generate('detailDocument', ['id' => $document->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($jsonLivre, Response::HTTP_CREATED, ["Location" => $location], true);
            //return new JsonResponse(['message' => 'Document ajouté avec succès.'], JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse(['error' => 'Fichier non trouvé dans la requête.'], JsonResponse::HTTP_BAD_REQUEST);
    }
}

