<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['contact:read']],
    denormalizationContext: ['groups' => ['contact:write']],
    operations: [
        // Récupérer un contact (sécurisé : uniquement propriétaire)
        new Get(security: "object.getOwner() == user"),

        // Récupérer la liste des contacts (accessible aux utilisateurs authentifiés)
        new GetCollection(security: "is_granted('ROLE_USER')"),

        // Créer un contact (authentifié), avec upload possible d'image
        new Post(
            security: "is_granted('ROLE_USER')",
            processor: \App\State\ContactProcessor::class,
            openapi: new Model\Operation(
                summary: 'Créer un nouveau contact',
                description: 'Crée un nouveau contact avec possibilité d\'upload d\'image de profil',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'firstname' => [
                                        'type' => 'string',
                                        'description' => 'Prénom du contact'
                                    ],
                                    'lastname' => [
                                        'type' => 'string',
                                        'description' => 'Nom du contact'
                                    ],
                                    'phone' => [
                                        'type' => 'string',
                                        'description' => 'Numéro de téléphone'
                                    ],
                                    'note' => [
                                        'type' => 'string',
                                        'description' => 'Note sur le contact'
                                    ],
                                    'profileImageFile' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'Image de profil (JPEG, PNG, WEBP - Max 2MB)'
                                    ]
                                ],
                                'required' => ['firstname', 'lastname', 'phone']
                            ]
                        ]
                    ])
                )
            ),
            deserialize: false
        ),

        // Mettre à jour un contact (propriétaire uniquement), possibilité d'uploader une nouvelle image
        new Put(
            security: "object.getOwner() == user",
            processor: \App\State\ContactProcessor::class,
            openapi: new Model\Operation(
                summary: 'Mettre à jour un contact',
                description: 'Met à jour un contact existant avec possibilité d\'upload d\'une nouvelle image de profil',
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'firstname' => [
                                        'type' => 'string',
                                        'description' => 'Prénom du contact'
                                    ],
                                    'lastname' => [
                                        'type' => 'string',
                                        'description' => 'Nom du contact'
                                    ],
                                    'phone' => [
                                        'type' => 'string',
                                        'description' => 'Numéro de téléphone'
                                    ],
                                    'note' => [
                                        'type' => 'string',
                                        'description' => 'Note sur le contact'
                                    ],
                                    'profileImageFile' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'Nouvelle image de profil (JPEG, PNG, WEBP - Max 2MB)'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            ),
            deserialize: false
        ),

        // Supprimer un contact (propriétaire uniquement)
        new Delete(
            security: "object.getOwner() == user",
            processor: \App\State\ContactProcessor::class
        )
    ]
)]
#[Vich\Uploadable]
class Contact
{
    // Identifiant unique du contact
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['contact:read'])]
    private ?int $id = null;

    // Prénom du contact (obligatoire)
    #[Groups(['contact:read', 'contact:write'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private ?string $firstname = null;

    // Nom du contact (obligatoire)
    #[Groups(['contact:read', 'contact:write'])]
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $lastname = null;

    // Numéro de téléphone unique (obligatoire)
    #[Groups(['contact:read', 'contact:write'])]
    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire.")]
    private ?string $phone = null;

    // Fichier d'image uploadé (non persisté en BDD, utilisé uniquement pour l'upload)
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        mimeTypesMessage: 'Merci de télécharger une image valide (jpeg, png, webp).'
    )]
    #[Vich\UploadableField(mapping: 'contact_images', fileNameProperty: 'profileImage')]
    #[Groups(['contact:write'])]
    private ?File $profileImageFile = null;

    // Nom du fichier image stocké en base (nullable)
    #[Groups(['contact:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profileImage = null;

    // Note libre sur le contact (nullable)
    #[Groups(['contact:read', 'contact:write'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    // Propriétaire du contact (relation ManyToOne vers User)
    #[Groups(['contact:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    // Date de dernière mise à jour (utile pour VichUploader et suivi)
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Appelé automatiquement avant suppression de l'entité.
     * Permet de déclencher la suppression du fichier via VichUploader.
     */
    #[ORM\PreRemove]
    public function preRemove(): void
    {
        // On libère la référence au fichier pour que VichUploader puisse le supprimer.
        if ($this->profileImageFile) {
            $this->profileImageFile = null;
        }
    }

    /**
     * Setter pour le fichier d'image uploadé.
     * Met à jour la date de modification pour que Doctrine détecte un changement.
     */
    public function setProfileImageFile(?File $image = null): void
    {
        $this->profileImageFile = $image;

        if (null !== $image) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getProfileImageFile(): ?File
    {
        return $this->profileImageFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Affecte automatiquement l'utilisateur connecté comme propriétaire.
     */
    public function setOwnerFromUser(UserInterface $user): void
    {
        if ($user instanceof User) {
            $this->owner = $user;
        }
    }

    // Getters et setters classiques

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
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

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): static
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
}
