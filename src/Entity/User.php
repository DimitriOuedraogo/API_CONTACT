<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\UserController;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')] // Le nom de table `user` entre backticks car mot réservé en SQL
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])] // Le téléphone est unique
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        // Route d'inscription publique
        new Post(
            uriTemplate: '/register',
            controller: UserController::class . '::register',
            security: "is_granted('PUBLIC_ACCESS')",
            securityMessage: 'Création de compte ouverte à tous.'
        ),
        // Route de connexion publique
        new Post(
            uriTemplate: '/login_check',
            controller: UserController::class . '::login',
            security: "is_granted('PUBLIC_ACCESS')",
            securityMessage: 'Connexion ouverte à tous.'
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read', 'user:write'])]
    #[ORM\Column(length: 20, unique: true)]
    private ?string $phone = null;

    /**
     * @var list<string> The user roles
     */
    #[Groups(['user:read'])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Groups(['user:write'])]
    #[ORM\Column]
    private ?string $password = null;

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Identifiant unique pour l’authentification (ici le téléphone)
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->phone;
    }

    /**
     * Retourne les rôles de l’utilisateur.
     * Garantit que tous les utilisateurs ont au moins ROLE_USER
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // rôle minimum

        return array_unique($roles);
    }

    /**
     * Définit les rôles (ex : ROLE_ADMIN, ROLE_USER, etc.)
     *
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Retourne le mot de passe hashé
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe hashé
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Sérialisation personnalisée pour éviter d’exposer le hash exact du mot de passe
     * (Symfony 7.3+ supporte ça, c’est une bonne pratique)
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        // On remplace le password par un hash CRC32C non réversible pour éviter fuite
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * Méthode héritée de UserInterface pour effacer les données sensibles
     * @deprecated - à supprimer avec Symfony 8
     */
    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // Rien à faire ici car on ne stocke pas de données sensibles en clair
    }
}
