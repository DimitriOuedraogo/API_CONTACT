# API_CONTACT
API Contacts - Symfony & API Platform
Cette API permet de gérer des contacts avec un système d’authentification sécurisé par JWT.
Chaque utilisateur peut créer, consulter, modifier et supprimer ses propres contacts, avec la possibilité d’ajouter une image de profil.

Fonctionnalités principales
Inscription et connexion sécurisées via JWT (authentification par téléphone et mot de passe)

Gestion complète des contacts (CRUD)

Upload d’image de profil avec validation (JPEG, PNG, WEBP, max 2MB)

Filtrage automatique des contacts pour ne voir que ceux appartenant à l’utilisateur connecté

Rôles utilisateur pour gérer les accès (ROLE_USER, ROLE_ADMIN)

Technologies utilisées
Symfony 6+

Apercu 
<img width="1340" height="639" alt="api_contact" src="https://github.com/user-attachments/assets/755778c4-5392-4c8d-b8de-608f169b046b" />


API Platform

Doctrine ORM

VichUploaderBundle pour la gestion des fichiers

LexikJWTAuthenticationBundle pour la sécurité JWT
