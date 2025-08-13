<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{
    /**
     * Enregistrement d'un nouvel utilisateur
     * 
     * @Route("/api/register", name="api_user_register", methods={"POST"})
     */
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], 400);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setPhone($data['phone'] ?? '');

        // Validation des données avant hashage
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Hachage du mot de passe si fourni
        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        } else {
            return new JsonResponse(['error' => 'Mot de passe requis'], 400);
        }

        // TODO: Ajouter ici logique métier (ex: envoi email, assignation rôles...)

        try {
            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'user' => [
                    'id' => $user->getId(),
                    'phone' => $user->getPhone(),
                    'roles' => $user->getRoles()
                ]
            ], 201);
        } catch (\Exception $e) {
            // Log l'erreur quelque part (ex: monolog) avant de renvoyer la réponse
            return new JsonResponse([
                'message' => 'Erreur lors de la création de l\'utilisateur'
            ], 500);
        }
    }

    /**
     * Authentification et génération d'un token JWT
     */
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Données JSON invalides'], 400);
            }

            // Validation simple des champs
            if (empty($data['phone']) || empty($data['password'])) {
                return new JsonResponse(['error' => 'Téléphone et mot de passe requis'], 400);
            }

            // Recherche utilisateur par téléphone
            $user = $em->getRepository(User::class)->findOneBy(['phone' => $data['phone']]);
            if (!$user) {
                return new JsonResponse(['error' => 'Identifiants invalides'], 401);
            }

            // Vérification du mot de passe
            if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
                return new JsonResponse(['error' => 'Identifiants invalides'], 401);
            }

            // Génération du token JWT
            $token = $jwtManager->create($user);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'phone' => $user->getPhone(),
                    'roles' => $user->getRoles()
                ]
            ], 200);

        } catch (\Exception $e) {
            // Log l'erreur pour débogage
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la connexion',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
