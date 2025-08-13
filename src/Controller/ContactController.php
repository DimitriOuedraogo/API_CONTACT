<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactController extends AbstractController
{
    /**
     * Créer un nouveau contact (lié automatiquement à l'utilisateur connecté)
     */
    public function createContact(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], 400);
        }

        // Vérifier les champs requis
        if (empty($data['firstname']) || empty($data['lastname']) || empty($data['phone'])) {
            return new JsonResponse(['error' => 'Prénom, nom et téléphone requis'], 400);
        }

        // Vérifier si le téléphone existe déjà pour cet utilisateur
        $existingContact = $em->getRepository(Contact::class)->findOneBy([
            'phone' => $data['phone'],
            'owner' => $currentUser
        ]);
        
        if ($existingContact) {
            return new JsonResponse(['error' => 'Ce numéro existe déjà dans vos contacts'], 409);
        }

        // Créer le contact
        $contact = new Contact();
        $contact->setFirstname($data['firstname']);
        $contact->setLastname($data['lastname']);
        $contact->setPhone($data['phone']);
        $contact->setOwner($currentUser); // Lier automatiquement à l'utilisateur connecté
        
        if (isset($data['profileImage'])) {
            $contact->setProfileImage($data['profileImage']);
        }
        if (isset($data['note'])) {
            $contact->setNote($data['note']);
        }

        // Validation
        $errors = $validator->validate($contact);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            $em->persist($contact);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Contact créé avec succès',
                'contact' => [
                    'id' => $contact->getId(),
                    'firstname' => $contact->getFirstname(),
                    'lastname' => $contact->getLastname(),
                    'phone' => $contact->getPhone(),
                    'profileImage' => $contact->getProfileImage(),
                    'note' => $contact->getNote()
                ]
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer tous les contacts de l'utilisateur connecté
     */
    public function getMyContacts(EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $contacts = $em->getRepository(Contact::class)->findBy(['owner' => $currentUser]);

        $contactsData = [];
        foreach ($contacts as $contact) {
            $contactsData[] = [
                'id' => $contact->getId(),
                'firstname' => $contact->getFirstname(),
                'lastname' => $contact->getLastname(),
                'phone' => $contact->getPhone(),
                'profileImage' => $contact->getProfileImage(),
                'note' => $contact->getNote()
            ];
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Contacts récupérés',
            'contacts' => $contactsData,
            'total' => count($contactsData)
        ], 200);
    }

    /**
     * Récupérer un contact spécifique (seulement si c'est le sien)
     */
    public function getContact(int $id, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $contact = $em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'owner' => $currentUser
        ]);

        if (!$contact) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        return new JsonResponse([
            'status' => 'success',
            'contact' => [
                'id' => $contact->getId(),
                'firstname' => $contact->getFirstname(),
                'lastname' => $contact->getLastname(),
                'phone' => $contact->getPhone(),
                'profileImage' => $contact->getProfileImage(),
                'note' => $contact->getNote()
            ]
        ], 200);
    }

    /**
     * Modifier un contact (seulement si c'est le sien)
     */
    public function updateContact(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $contact = $em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'owner' => $currentUser
        ]);

        if (!$contact) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données JSON invalides'], 400);
        }

        // Mise à jour des champs
        if (isset($data['firstname'])) {
            $contact->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $contact->setLastname($data['lastname']);
        }
        if (isset($data['phone'])) {
            // Vérifier unicité du nouveau téléphone
            $existingContact = $em->getRepository(Contact::class)->findOneBy([
                'phone' => $data['phone'],
                'owner' => $currentUser
            ]);
            
            if ($existingContact && $existingContact->getId() !== $contact->getId()) {
                return new JsonResponse(['error' => 'Ce numéro existe déjà dans vos contacts'], 409);
            }
            
            $contact->setPhone($data['phone']);
        }
        if (isset($data['profileImage'])) {
            $contact->setProfileImage($data['profileImage']);
        }
        if (isset($data['note'])) {
            $contact->setNote($data['note']);
        }

        // Validation
        $errors = $validator->validate($contact);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Contact mis à jour avec succès',
                'contact' => [
                    'id' => $contact->getId(),
                    'firstname' => $contact->getFirstname(),
                    'lastname' => $contact->getLastname(),
                    'phone' => $contact->getPhone(),
                    'profileImage' => $contact->getProfileImage(),
                    'note' => $contact->getNote()
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un contact (seulement si c'est le sien)
     */
    public function deleteContact(int $id, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }

        $contact = $em->getRepository(Contact::class)->findOneBy([
            'id' => $id,
            'owner' => $currentUser
        ]);

        if (!$contact) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        try {
            $em->remove($contact);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Contact supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}