<?php
namespace App\Doctrine;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Contact;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Cette extension API Platform filtre les requêtes Doctrine ORM
 * pour ne retourner que les contacts appartenant à l'utilisateur connecté,
 * sauf si l'utilisateur a le rôle ADMIN.
 */
class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security) {}

    /**
     * Appliqué aux collections (ex : GET /contacts)
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Appliqué aux items uniques (ex : GET /contacts/{id})
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Logique commune pour ajouter la condition WHERE sur l'owner,
     * sauf si utilisateur admin ou pas connecté
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // On cible uniquement l'entité Contact
        if ($resourceClass !== Contact::class) {
            return;
        }

        $user = $this->security->getUser();

        // Si admin ou pas connecté, on ne filtre pas
        if ($this->security->isGranted('ROLE_ADMIN') || !$user) {
            return;
        }

        // Récupère l'alias de la requête principale (ex: "o")
        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Ajoute la condition "o.owner = :current_user"
        $queryBuilder
            ->andWhere(sprintf('%s.owner = :current_user', $rootAlias))
            ->setParameter('current_user', $user);
    }
}
