# Couche persistance & services utilisateurs - Phase 1

## Vue d'ensemble

Cette implémentation fournit une couche complète de persistance et de services pour l'authentification des utilisateurs dans le projet Toubilib, suivant les principes de Clean Architecture.

## Architecture

### Entités de domaine

- `User` : Entité principale représentant un utilisateur
- `UserRole` : Énumération des rôles utilisateur (ADMIN=0, USER=1)

### DTOs (Data Transfer Objects)

- `UserDTO` : Objet de transfert pour les données utilisateur (sans mot de passe)

### Ports (Interfaces)

- `UserRepositoryInterface` : Interface du repository pour les opérations de persistance

### Adaptateurs

- `PDOUserRepository` : Implémentation PDO du repository utilisateur

### Services applicatifs

- `ServiceAuthInterface` : Interface du service d'authentification
- `ServiceAuth` : Implémentation du service avec JWT

## Base de données

### Configuration

La base de données `toubiauth` contient la table `users` avec la structure suivante :

```sql
CREATE TABLE "public"."users" (
    "id" uuid NOT NULL,
    "email" character varying(128) NOT NULL,
    "password" character varying(256) NOT NULL,
    "role" smallint DEFAULT '0' NOT NULL,
    CONSTRAINT "users_email" UNIQUE ("email"),
    CONSTRAINT "users_id" PRIMARY KEY ("id")
);
```

### Variables d'environnement

Ajoutez dans votre fichier `.env` :

```env
# Base de données Auth
AUTH_DB_HOST=toubiauth.db
AUTH_DB_PORT=5432
AUTH_DB_NAME=toubiauth
AUTH_DB_USER=toubiauth
AUTH_DB_PASS=toubiauth

# JWT Configuration
AUTH_JWT_SECRET=your-super-secret-jwt-key-change-in-production
AUTH_JWT_EXPIRATION=3600
AUTH_JWT_ISSUER=toubilib
```

## Utilisation

### Service d'authentification

```php
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\api\security\JwtManagerInterface;

// Injection via DI container
/** @var ServiceAuthInterface $authService */
$authService = $container->get(ServiceAuthInterface::class);

// Authentification
try {
    $userDTO = $authService->authenticate('user@example.com', 'password');
    echo "Utilisateur authentifié: " . $userDTO->email;
} catch (InvalidCredentialsException $e) {
    echo "Identifiants incorrects";
}

// Génération et vérification de JWT via le manager dédié
/** @var JwtManagerInterface $jwtManager */
$jwtManager = $container->get(JwtManagerInterface::class);
$accessToken = $jwtManager->createAccessToken($userDTO);

try {
    $payload = $jwtManager->decode($accessToken, 'access');
    $verifiedUser = $authService->getUserById($payload->subject);
    echo "Token valide pour: " . $verifiedUser->email;
} catch (\toubilib\api\security\InvalidTokenException $e) {
    echo "Token invalide ou expiré";
}
```

### Repository utilisateur

```php
use toubilib\core\application\ports\UserRepositoryInterface;

/** @var UserRepositoryInterface $userRepo */
$userRepo = $container->get(UserRepositoryInterface::class);

// Créer un utilisateur
$newUser = $userRepo->createUser(
    'nouveau@example.com',
    'motdepasse123',
    UserRole::USER
);

// Trouver un utilisateur
try {
    $user = $userRepo->findByEmail('user@example.com');
    $user = $userRepo->findById('user-uuid');
} catch (UserNotFoundException $e) {
    echo "Utilisateur non trouvé";
}
```

## Scripts utilitaires

### Créer un utilisateur administrateur

```bash
php test/Auth/create_admin.php
```

### Test d'intégration complet

```bash
php test/Auth/integration_test.php
```

### Tests unitaires

```bash
./vendor/bin/phpunit test/Auth/Unit/
```

## Sécurité

- **Mots de passe** : Hashés avec `password_hash()` (bcrypt par défaut)
- **JWT** : Signé avec HS256, expiration configurable
- **Requêtes SQL** : Préparées pour éviter les injections SQL
- **Validation** : Vérification des emails et contraintes de base

## Gestion des erreurs

### Exceptions spécifiques

- `UserNotFoundException` : Utilisateur non trouvé
- `DuplicateUserException` : Email déjà utilisé
- `InvalidCredentialsException` : Identifiants invalides ou token JWT invalide

### Codes d'erreur PDO

- `23505` : Violation de contrainte d'unicité (email dupliqué)

## Tests

### Coverage

- Entités de domaine : `User`, `UserRole`
- DTOs : `UserDTO`
- Repository : `PDOUserRepository`
- Services : `ServiceAuth`

### Types de tests

- **Tests unitaires** : Logique métier et interactions avec mocks
- **Tests d'intégration** : Fonctionnement complet avec base de données

### Fixtures

Les données de test utilisent les utilisateurs existants dans `toubiauth.data.sql`.

## Dépendances

### Nouvelles dépendances ajoutées

- `firebase/php-jwt` : Gestion des tokens JWT (déjà présent)
- `ramsey/uuid` : Génération d'UUID (déjà présent)

### Configuration DI

Les services sont enregistrés dans `config/di/services.php` :

- `UserRepositoryInterface` → `PDOUserRepository`
- `ServiceAuthInterface` → `ServiceAuth`
- Configuration PDO pour la base `toubiauth`

## Autoloader

Exécutez après l'implémentation :

```bash
composer dump-autoload
```

## Namespaces

```
toubilib\core\domain\entities\user      # Entités User
toubilib\core\domain\exceptions         # Exceptions métier
toubilib\core\application\dto            # DTOs
toubilib\core\application\ports          # Interfaces repository
toubilib\core\application\usecases       # Services applicatifs
toubilib\infra\repositories              # Implémentations repository
```
