<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Contact;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Storage\StorageInterface;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

class ContactProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private StorageInterface $storage
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Gestion de la suppression
        if ($operation instanceof Delete) {
            return $this->processDelete($data);
        }

        // Gestion de la création et mise à jour
        if ($operation instanceof Post) {
            return $this->processCreate($context);
        }

        if ($operation instanceof Put) {
            return $this->processUpdate($data, $context);
        }

        throw new BadRequestHttpException('Opération non supportée');
    }

    private function processCreate(array $context): Contact
    {
        $request = $context['request'] ?? null;
        
        if (!$request instanceof Request) {
            throw new BadRequestHttpException('Request not found');
        }

        // Récupération des données du formulaire
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $phone = $request->request->get('phone');
        $note = $request->request->get('note');
        
        // Validation des champs obligatoires
        if (!$firstname || !$lastname || !$phone) {
            throw new BadRequestHttpException('Les champs firstname, lastname et phone sont obligatoires');
        }

        // Création du contact
        $contact = new Contact();
        $contact->setFirstname($firstname);
        $contact->setLastname($lastname);
        $contact->setPhone($phone);
        $contact->setNote($note);

        // Attribution de l'utilisateur connecté comme owner
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            $contact->setOwner($currentUser);
        } else {
            throw new BadRequestHttpException('Utilisateur non authentifié');
        }

        // Gestion du fichier uploadé
        $this->handleFileUpload($contact, $request);

        // Persistance en base
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    private function processUpdate(Contact $contact, array $context): Contact
    {
        $request = $context['request'] ?? null;
        
        if (!$request instanceof Request) {
            throw new BadRequestHttpException('Request not found');
        }

        // Mise à jour des champs si présents
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $phone = $request->request->get('phone');
        $note = $request->request->get('note');

        if ($firstname) {
            $contact->setFirstname($firstname);
        }
        if ($lastname) {
            $contact->setLastname($lastname);
        }
        if ($phone) {
            $contact->setPhone($phone);
        }
        // Note peut être vide, on la met à jour même si elle est null
        $contact->setNote($note);

        // Gestion du fichier uploadé (remplace l'ancien s'il existe)
        $this->handleFileUpload($contact, $request);

        // Persistance en base
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    private function processDelete(Contact $contact): void
    {
        // Supprimer le fichier image s'il existe avant de supprimer l'entité
        if ($contact->getProfileImage()) {
            // Supprimer physiquement le fichier du système de fichiers
            $this->storage->remove($contact, 'profileImageFile');
        }

        // Supprimer l'entité de la base de données
        $this->entityManager->remove($contact);
        $this->entityManager->flush();
    }

    private function handleFileUpload(Contact $contact, Request $request): void
    {
        $uploadedFile = $request->files->get('profileImageFile');
        
        if ($uploadedFile instanceof UploadedFile) {
            // Validation du fichier
            if (!$uploadedFile->isValid()) {
                throw new BadRequestHttpException('Le fichier uploadé n\'est pas valide');
            }

            // Vérification du type MIME
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
                throw new BadRequestHttpException('Type de fichier non autorisé. Utilisez JPEG, PNG ou WEBP');
            }

            // Vérification de la taille (2MB max)
            if ($uploadedFile->getSize() > 2 * 1024 * 1024) {
                throw new BadRequestHttpException('Le fichier est trop volumineux (2MB maximum)');
            }

            // Attribution du fichier au contact (VichUploader s'occupe du reste)
            // Pour la mise à jour, VichUploader supprimera automatiquement l'ancien fichier
            $contact->setProfileImageFile($uploadedFile);
        }
    }
}