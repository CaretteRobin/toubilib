# Cadrage Fonctionnel & Sécurité - API Toubilib

Ce document formalise les exigences fonctionnelles pour la sécurisation de l'API Toubilib, conformément au plan du TD1.5.

## 1. Personas et Permissions

Trois personas principaux sont identifiés pour l'application :

| Persona | Rôle Technique | Cas d'usage principaux |
| :--- | :--- | :--- |
| **Patient** | `ROLE_PATIENT` | - S'authentifier<br>- Consulter les praticiens<br>- Consulter les disponibilités<br>- Prendre, consulter et annuler **ses propres** rendez-vous. |
| **Praticien** | `ROLE_PRATICIEN` | - S'authentifier<br>- Consulter et gérer **son propre** agenda (lister, honorer, marquer absent)<br>- Consulter les détails des patients ayant RDV avec lui. |
| **Admin** | `ROLE_ADMIN` | - Accès en lecture seule à toutes les données pour le support et la maintenance.<br>- (Périmètre à affiner si besoin de plus de droits). |

## 2. Flux d'Authentification

L'authentification se basera sur un flux de jeton JWT (JSON Web Token).

1.  **Login** : L'utilisateur (patient ou praticien) envoie ses identifiants (email/mot de passe) via une requête `POST /login`.
2.  **Validation** : Le service d'authentification vérifie les identifiants dans la base `toubiauth.db`.
3.  **Génération du Token** : Si la validation réussit, le service génère un JWT signé.
4.  **Requêtes authentifiées** : Pour toutes les requêtes suivantes sur des routes sécurisées, le client doit inclure le token dans l'en-tête `Authorization: Bearer <token>`.
5.  **Validation du Token** : L'API Gateway (ou un middleware) intercepte chaque requête, valide la signature et l'expiration du token, puis extrait les informations de l'utilisateur.

### Claims du JWT

Le payload (contenu) du JWT devra contenir à minima :

```json
{
  "sub": "uuid-de-l-utilisateur", // Subject (ID unique de l'utilisateur)
  "roles": ["ROLE_PATIENT"],      // Rôles de l'utilisateur
  "iat": 1678886400,              // Issued At (timestamp de création)
  "exp": 1678890000               // Expiration Time (timestamp d'expiration)
}
