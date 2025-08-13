<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Ne toucher qu’aux requêtes API JSON (adapter selon ton projet)
        if (0 !== strpos($request->getPathInfo(), '/api')) {
            return;
        }

        // Ignore si la réponse n’est pas JSON
        if (false === strpos($response->headers->get('Content-Type'), 'json')) {
            return;
        }

        // Décoder le contenu JSON original
        $content = json_decode($response->getContent(), true);

        // Messages custom selon le status HTTP
        $messages = [
            200 => 'Requête réussie.',
            201 => 'Création réussie.',
            204 => 'Aucune donnée.',
            400 => 'Requête invalide.',
            401 => 'Non autorisé.',
            403 => 'Accès interdit.',
            404 => 'Ressource non trouvée.',
            500 => 'Erreur interne du serveur.',
        ];

        $status = $response->getStatusCode();
        $message = $messages[$status] ?? 'Statut HTTP ' . $status;

        // Construire la nouvelle enveloppe de réponse
        $data = [
            'status' => $status,
            'message' => $message,
            'data' => $content,
        ];

        // IMPORTANT : Créer une nouvelle JsonResponse avec le bon status
        $newResponse = new JsonResponse($data, $status);
        
        // Copier les headers importants
        $newResponse->headers->add($response->headers->all());
        $newResponse->headers->set('Content-Type', 'application/json');

        // Remplacer la réponse
        $event->setResponse($newResponse);
    }
}
