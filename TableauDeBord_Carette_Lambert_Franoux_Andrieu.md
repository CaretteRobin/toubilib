======================================================================
TABLEAU DE BORD – PROJET TOUBILIB (TD 1 → 5)
Robin Carette · Valentino Lambert · Noé Franoux · Paul Andrieu
Lien du repo github : https://github.com/CaretteRobin/toubilib.git
======================================================================

1. Répartition des réalisations
--------------------------------
Robin
  - Mise en place du conteneur PHP-DI (bootstrap, settings/services/api) et intégration continue locale
  - Implémentation métier rendez-vous (création, annulation, agenda) et validations applicatives
  - Séparation complète de la gestion JWT côté API : `JwtManager`, provider d’authentification, middlewares
  - Refonte des autorisations (middleware + `AuthorizationService`) pour couvrir les politiques du sujet
  - Industrialisation CORS (middleware configurable) et harmonisation des réponses d’erreur
  - Coordination tests Bruno, scénarios manuels et mise à jour de la documentation

Valentino
  - Refonte repository `PDOPraticienRepository` + mapping entités/DTO praticiens
  - Actions Slim pour la liste/détail praticiens + endpoints agenda/créneaux (partie présentation)
  - Participation aux DTO praticien (base + détail) et revue des conventions REST/HATEOAS
  - Contribution au tableau de suivi et présence sur les revues de code (validation Respect\Validation)

Noé
  - Repository `PDORdvRepository` : requêtes Postgres, détection des chevauchements, persistance
  - Optimisation SQL et scripts fixtures ; validation des jeux de données multi-bases
  - Support sur la couche application RDV (conversion entité→DTO) et vérification des tests Bruno
  - Préparation des environnements Docker (volumes BDD, variables d’env) et contrôle de cohérence

Paul
  - Modélisation domaine (`Rdv`, `Praticien`, structure, motifs, moyens de paiement)
  - DTO application (`RdvDTO`, `PraticienDTO`, `PraticienDetailDTO`, etc.) et premières routes REST
  - Scaffold API Slim (routes initiales, gestion des erreurs, base du middleware RequireRole)
  - Documentation sur l’architecture hexagonale + aide à la configuration Docker Compose

2. Fonctionnalités livrées (TD1 → TD5)
---------------------------------------
TD1        – Conteneur DI, structure hexagonale, premières routes REST  
TD1.2      – Injection explicite (services/repos/actions), listage praticiens + détail  
TD1.3 p1   – Middleware création DTO, service création RDV avec validations métier  
TD1.3 p2   – Annulation RDV, consultation agenda praticien, DTO agenda/occupations  
TD1.5      – Authn/Authz JWT externalisée (JwtManager API), CORS configurable, contrôle d’autorisation complet (création/annulation RDV, consultation agenda)  
Tests      – Validation Respect\Validation, scénarios Bruno/HTTPie, journalisation Slim contrôlée

3. Routes API testables (prêtes Bruno/HTTPie)
----------------------------------------------
GET  /praticiens  
GET  /praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363  
GET  /praticiens/inconnu                           -> 400 (UUID invalide)  
GET  /praticiens/00000000-0000-0000-0000-000000000000 -> 404  
GET  /praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363/rdv/occupes?de=2025-12-01&a=2025-12-07  
GET  /praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363/rdv/occupes?de=01-12-2025&a=07-12-2025 -> 400  
GET  /praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363/agenda?de=2025-12-01&a=2025-12-07  
GET  /praticiens/4305f5e9-be5a-4ccf-8792-7e07d7017363/agenda           -> nécessite token praticien/admin  
GET  /rdv/2e1a7275-2593-3c04-9a4c-4e7cbada9541                         -> nécessite token propriétaire  
GET  /auth/me                                                          -> nécessite token access

POST /auth/login  (body email/password, retourne access+refresh TTL)  
POST /rdv         (patient authentifié)  
  {
    "praticien_id": "4305f5e9-be5a-4ccf-8792-7e07d7017363",
    "patient_id":   "5abcdbc4-90c9-3b86-82a3-c4cf1f7377d0",
    "motif_id":     "5",
    "date_heure_debut": "2025-02-10 10:00:00",
    "duree": 30
  }
POST /rdv         -> 403 si patient_id ≠ utilisateur authentifié  
POST /rdv         -> 422 si créneau indisponible / motif interdit  

DELETE /rdv/2e1a7275-2593-3c04-9a4c-4e7cbada9541  -> succès pour praticien/patient propriétaire ou admin  
DELETE /rdv/...                                   -> 403 si rôle non autorisé / identité différente  

Identifiants utiles :  
  Praticien radiologie : 4305f5e9-be5a-4ccf-8792-7e07d7017363  
  Patient (Marguerite Alves) : 5abcdbc4-90c9-3b86-82a3-c4cf1f7377d0  
  Motifs autorisés : 5 (radiologie), 6 (échographie), 7 (scanner), 8 (IRM)  
  RDV existant : 2e1a7275-2593-3c04-9a4c-4e7cbada9541

4. Synthèse finale
-------------------
Statut : fonctionnalités 1 → 8 du sujet détaillé couvertes, avec conformité aux politiques d’autorisation et aux principes des cours (JWT externalisé, CORS, middlewares).  
Tests : scénarios Bruno/HTTPie + contrôles manuels post refonte Auth/JWT.  
Dernier commit : `feat: externaliser la gestion JWT et sécuriser la politique rendez-vous`.
