<?php

namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Contact;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Persister personnalisé pour l'entité Contact.
 * 
 * Avant de persister, affecte automatiquement l'utilisateur connecté
 * comme propriétaire du contact si ce n'est pas déjà fait.
 */
class ContactDataPersister implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private Security $security
    ) {}

    /**
     * @param mixed $data L'entité à persister
     * @param Operation $operation L'opération ApiPlatform en cours
     * @param array $uriVariables Variables d'URL (ex: id)
     * @param array $context Contexte additionnel
     * 
     * @return mixed Résultat de la persistance déléguée
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Si c'est un Contact et qu'il n'a pas d'owner
        if ($data instanceof Contact && null === $data->getOwner()) {
            $user = $this->security->getUser();

            // Si utilisateur connecté, on le définit comme owner
            if ($user) {
                $data->setOwner($user);
            }
        }

        // Délègue la persistance au service décoré (ex: Doctrine)
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
