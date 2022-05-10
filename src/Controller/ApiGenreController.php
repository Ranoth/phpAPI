<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiGenreController extends AbstractController
{
    #[Route('/api/genres', name: 'api_genres', methods: 'GET')]
    public function list(GenreRepository $repo, SerializerInterface $serializer): Response
    {
        $genres = $repo->findAll();
        $result = $serializer->serialize(
            $genres,
            'json',
            [
                'groups' => ['listeGenreFull']
            ]
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/genres/{id}', name: 'api_genres_show', methods: 'GET')]
    public function show(Genre $genre, SerializerInterface $serializer): Response
    {
        $result = $serializer->serialize(
            $genre,
            'json',
            [
                'groups' => ['listeGenreSimple']
            ]
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/genres', name: 'api_genres_create', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        // $genre = new Genre();
        // $serializer->deserialize($data, Genre::class, 'json', ['object_to_populate' => $genre]);

        // gestion des erreurs de validation
        $genre = $serializer->deserialize($data, Genre::class, 'json');
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($genre);
        $manager->flush();
        
        return new JsonResponse(
            'Le genre à bien été créé',
            Response::HTTP_CREATED,
            [
                'location' => 'api/genres/'.$genre->getId()
                // 'location' => $this->generateUrl('api_genres_show', ['id' => $genre->getId(), UrlGeneratorInterface::ABSOLUTE_URL]),
            ],
            true
        );
    }

    #[Route('/api/genres/{id}', name: 'api_genres_update', methods: 'PUT')]
    public function edit(Genre $genre, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $result = $serializer->deserialize(
            $data,
            Genre::class,
            'json',
            ['object_to_populate' => $genre]
        );

        // gestion des erreurs de validation
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($genre);
        $manager->flush();
    
        return new JsonResponse(
            'Le genre à bien été modifié',
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/genres/{id}', name: 'api_genres_delete', methods: 'DELETE')]
    public function delete(Genre $genre, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer): Response
    {
        $manager->remove($genre);
        $manager->flush();
    
        return new JsonResponse(
            'Le genre à bien été supprimé',
            Response::HTTP_OK,
            []
        );
    }
}
