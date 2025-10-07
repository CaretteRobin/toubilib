# Toubilib API

API REST pour la gestion de prise de rendez-vous médicaux entre patients et praticiens.

## Table des matières

- [Architecture](#architecture)
- [Démarrage rapide](#démarrage-rapide)
- [Sécurité et Authentification (Plan TD1.5)](#sécurité-et-authentification-plan-td15)
  - [Flux d'Authentification](#flux-dauthentification)
  - [Rôles Utilisateurs](#rôles-utilisateurs)
  - [Matrice des Permissions](#matrice-des-permissions)
  - [Documentation HATEOAS](#documentation-hateoas)
- [Plan de Développement (Tâches restantes)](#plan-de-développement-tâches-restantes)

## Architecture

Le projet suit une architecture orientée services, orchestrée avec Docker Compose.

| Service | Port | Description |
| :--- | :--- | :--- |
| `api.toubilib` | `6080` | Service principal de l'API, écrit en PHP. |
| `toubiprati.db` | `5432` | Base de données PostgreSQL pour les données des praticiens. |
| `toubipatient.db`| `5433` | Base de données PostgreSQL pour les données des patients. |
| `toubirdv.db` | `5434` | Base de données PostgreSQL pour les rendez-vous. |
| `toubiauth.db` | `5435` | Base de données PostgreSQL dédiée à l'authentification (utilisateurs, rôles). |
| `adminer` | `8080` | Outil d'administration web pour les bases de données. |

## Démarrage rapide

1.  Assurez-vous que Docker et Docker Compose sont installés sur votre machine.
2.  Clonez le dépôt.
3.  À la racine du projet, lancez la commande suivante pour construire et démarrer tous les services :

    ```bash
    docker-compose up -d --build
    ```

4.  L'API est maintenant accessible à l'adresse `http://localhost:6080`.
5.  L'interface Adminer est accessible à `http://localhost:8080` pour gérer les bases de données.

## Sécurité et Authentification (Plan TD1.5)

Cette section détaille le plan fonctionnel pour la sécurisation de l'API.

### Flux d'Authentification

L'authentification se base sur un flux de jeton **JWT (JSON Web Token)**.

1.  **Login** : L'utilisateur envoie ses identifiants (`email`/`password`) via une requête `POST /login`.
2.  **Validation** : Le service d'authentification vérifie les identifiants dans la base `toubiauth.db`.
3.  **Génération du Token** : En cas de succès, le service génère un JWT signé contenant les informations de l'utilisateur.
4.  **Requêtes authentifiées** : Pour toutes les requêtes sur des routes sécurisées, le client doit inclure le token dans l'en-tête `Authorization: Bearer <token>`.

#### Claims du JWT

Le payload du JWT contiendra à minima :

```json
{
  "sub": "uuid-de-l-utilisateur", // Subject (ID unique de l'utilisateur)
  "roles": ["ROLE_PATIENT"],      // Rôles de l'utilisateur
  "iat": 1678886400,              // Issued At (timestamp de création)
  "exp": 1678890000               // Expiration Time (timestamp d'expiration)
}
```

### Rôles Utilisateurs

| Persona | Rôle Technique | Permissions principales |
| :--- | :--- | :--- |
| **Patient** | `ROLE_PATIENT` | Consulter praticiens, prendre/consulter/annuler **ses propres** RDV. |
| **Praticien** | `ROLE_PRATICIEN` | Gérer **son propre** agenda, consulter les détails de ses patients. |
| **Admin** | `ROLE_ADMIN` | Accès en lecture seule à toutes les données pour le support. |

### Matrice des Permissions

| Route | Méthode | Description | Rôle Requis | Contrôle d'Accès Spécifique (ACL) |
| :--- | :--- | :--- | :--- | :--- |
| `/login` | `POST` | Authentification | Public | N/A |
| `/praticiens` | `GET` | Lister les praticiens | `ROLE_PATIENT` | Aucun |
| `/praticiens/{id}` | `GET` | Détail d'un praticien | `ROLE_PATIENT` | Aucun |
| `/praticiens/{id}/creneaux-occupes` | `GET` | Voir les créneaux occupés | `ROLE_PATIENT` | Aucun |
| `/rdv` | `POST` | Créer un rendez-vous | `ROLE_PATIENT` | Le `patientId` doit être celui de l'utilisateur authentifié. |
| `/rdv/{id}` | `GET` | Consulter un RDV | `ROLE_PATIENT`, `ROLE_PRATICIEN` | L'utilisateur doit être le patient **OU** le praticien du RDV. |
| `/rdv/{id}/cancel` | `POST` | Annuler un RDV | `ROLE_PATIENT`, `ROLE_PRATICIEN` | L'utilisateur doit être le patient **OU** le praticien du RDV. |
| `/praticiens/{id}/agenda` | `GET` | Lister l'agenda d'un praticien | `ROLE_PRATICIEN` | L'ID du praticien `{id}` doit correspondre à l'ID de l'utilisateur authentifié. |
| `/rdv/{id}/honor` | `POST` | Marquer un RDV comme honoré | `ROLE_PRATICIEN` | L'utilisateur doit être le praticien du RDV. |
| `/rdv/{id}/noshow` | `POST` | Marquer une absence | `ROLE_PRATICIEN` | L'utilisateur doit être le praticien du RDV. |

### Documentation HATEOAS

Les réponses de l'API seront enrichies avec des liens hypermédia (`_links`) pour guider le client sur les actions possibles en fonction de son rôle et de l'état de la ressource.

**Exemple pour `GET /rdv/{id}` (vu par un patient) :**

```json
{
  "id": "uuid-du-rdv",
  "status": "scheduled",
  // ... autres données
  "_links": {
    "self": { "href": "/rdv/uuid-du-rdv" },
    "cancel": { "href": "/rdv/uuid-du-rdv/cancel", "method": "POST" },
    "praticien": { "href": "/praticiens/uuid-du-praticien" }
  }
}
```

## Plan de Développement (Tâches restantes)

Voici la liste des tâches à réaliser pour implémenter la sécurité et finaliser les fonctionnalités.

-   **Architecture & Sécurité**
    -   [ ] **Auth Service**: Mettre en place un service/middleware d'authentification qui valide les tokens JWT.
    -   [ ] **Auth DB**: Créer le schéma de la base de données `toubiauth.db` et les données initiales.
    -   [ ] **Login Endpoint**: Développer le endpoint `POST /login`.
    -   [ ] **ACL**: Implémenter la logique de contrôle d'accès (ACL) dans les services pour vérifier les permissions spécifiques (ex: un patient ne peut annuler que son propre RDV).

-   **Use Cases `ServiceRDV`**
    -   [ ] **`creerRendezVous`**: Modifier pour que l'ID du patient soit extrait du token d'authentification.
    -   [ ] **`annulerRendezVous`**: Sécuriser pour vérifier que l'utilisateur est le patient ou le praticien du RDV.
    -   [ ] **`listerAgenda`**: Sécuriser pour vérifier que l'ID du praticien correspond à celui de l'utilisateur authentifié.
    -   [ ] **`honorerRendezVous` / `marquerRendezVousAbsent`**: Sécuriser pour qu'ils ne soient accessibles qu'au praticien du RDV.

-   **API & Documentation**
    -   [ ] **HATEOAS**: Enrichir les réponses API avec des liens `_links` conditionnels.
    -   [ ] **OpenAPI/Swagger**: Mettre à jour la documentation d'API pour refléter les routes sécurisées, les rôles requis et les nouvelles réponses.