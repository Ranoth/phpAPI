<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use App\Repository\NationaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiAuteurController extends AbstractController
{
    #[Route('/api/auteurs', name: 'api_auteurs', methods: 'GET')]
    public function list(AuteurRepository $repo, SerializerInterface $serializer): Response
    {
        $auteurs = $repo->findAll();
        $result = $serializer->serialize(
            $auteurs,
            'json',
            [
                'groups' => ['listeAuteurFull']
            ]
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/auteurs/{id}', name: 'api_auteurs_show', methods: 'GET')]
    public function show(Auteur $auteur, SerializerInterface $serializer): Response
    {
        $result = $serializer->serialize(
            $auteur,
            'json',
            [
                'groups' => ['listeAuteurSimple']
            ]
        );

        return new JsonResponse(
            $result,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/auteurs', name: 'api_auteurs_create', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $manager, NationaliteRepository $repoNation, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $auteur = new Auteur();
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        $serializer->deserialize(
            $data,
            Auteur::class,
            'json',
            ['object_to_populate' => $auteur]
        );
        $auteur->setNationalite($nationalite);

        // gestion des erreurs de validation
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($auteur);
        $manager->flush();
        
        return new JsonResponse(
            "L'auteur à bien été créé",
            Response::HTTP_CREATED,
            [
                'location' => 'api/auteurs/'.$auteur->getId()
                // 'location' => $this->generateUrl('api_auteurs_show', ['id' => $auteur->getId(), UrlGeneratorInterface::ABSOLUTE_URL]),
            ],
            true
        );
    }

    // Compris à l'aide de la commande debug:router, j'ai oublié lors de la copie de renommer la route de la méthode delete en 'api_auteurs_delete', mais ça n'a pas
    // posé de problèmes au départ car le cache PHP n'avait pas été vidé à ce moment là. J'ai ensuite fait une migration quand je me suis rendu compte que 'nationalite'
    // s'appelait 'relation' pour renommer le champ dans la base de donnée, ce qui à aussi vidé le cache PHP et à partir de là les routes ne fonctionnaient plus.
    #[Route('/api/auteurs/{id}', name: 'api_auteurs_update', methods: 'PUT')]
    public function edit(Auteur $auteur, NationaliteRepository $repoNation, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        $serializer->deserialize(
            $data,
            Auteur::class,
            'json',
            ['object_to_populate' => $auteur]
        );
        $auteur->setNationalite($nationalite);

        // gestion des erreurs de validation
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($auteur);
        $manager->flush();
    
        return new JsonResponse(
            "L'auteur à bien été modifié",
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/auteurs/{id}', name: 'api_auteurs_delete', methods: 'DELETE')]
    public function delete(Auteur $auteur, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer): Response
    {
        $manager->remove($auteur);
        $manager->flush();
    
        return new JsonResponse(
            "L'auteur à bien été supprimé",
            Response::HTTP_OK,
            []
        );
    }
}
