<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\exceptions\InvalidCredentialsException;

echo "=== Test d'intégration Auth ===\n\n";

try {
    // Bootstrap Slim app and container
    $app = require_once __DIR__ . '/../../config/bootstrap.php';
    $container = $app->getContainer();

    /** @var ServiceAuthInterface $authService */
    $authService = $container->get(ServiceAuthInterface::class);
    
    /** @var UserRepositoryInterface $userRepository */
    $userRepository = $container->get(UserRepositoryInterface::class);

    echo "✓ Services correctement injectés via DI\n\n";

    // Test 1: Authentification avec un utilisateur existant
    echo "Test 1: Authentification utilisateur existant\n";
    try {
        $userDTO = $authService->authenticate('Denis.Teixeira@hotmail.fr', 'password'); // Mot de passe par défaut des fixtures
        echo "✗ Échec: Authentification réussie avec mot de passe par défaut (vérifier les fixtures)\n";
    } catch (InvalidCredentialsException $e) {
        echo "✓ Succès: Authentification échouée comme attendu (mot de passe incorrect)\n";
    }

    // Test 2: Récupération d'un utilisateur par email
    echo "\nTest 2: Récupération utilisateur par email\n";
    try {
        $userDTO = $authService->getUserByEmail('Denis.Teixeira@hotmail.fr');
        echo "✓ Succès: Utilisateur récupéré - ID: {$userDTO->id}, Email: {$userDTO->email}, Role: {$userDTO->role}\n";
        
        // Test 3: Génération de token JWT
        echo "\nTest 3: Génération token JWT\n";
        $token = $authService->generateJwtToken($userDTO);
        echo "✓ Succès: Token généré (longueur: " . strlen($token) . " caractères)\n";
        
        // Test 4: Vérification du token JWT
        echo "\nTest 4: Vérification token JWT\n";
        $verifiedUser = $authService->verifyJwtToken($token);
        echo "✓ Succès: Token vérifié - Utilisateur: {$verifiedUser->email}\n";
        
    } catch (Exception $e) {
        echo "✗ Erreur: " . $e->getMessage() . "\n";
    }

    // Test 5: Test du repository directement
    echo "\nTest 5: Test repository direct\n";
    try {
        $user = $userRepository->findById('d975aca7-50c5-3d16-b211-cf7d302cba50');
        echo "✓ Succès: Utilisateur trouvé par ID - Email: {$user->email}\n";
    } catch (Exception $e) {
        echo "✗ Erreur repository: " . $e->getMessage() . "\n";
    }

    // Test 6: Création d'un nouvel utilisateur
    echo "\nTest 6: Création d'un nouvel utilisateur\n";
    try {
        $newUserDTO = $authService->createUser('test@toubilib.com', 'testpassword123', UserRole::USER);
        echo "✓ Succès: Nouvel utilisateur créé - ID: {$newUserDTO->id}\n";
        
        // Test 7: Authentification avec le nouvel utilisateur
        echo "\nTest 7: Authentification avec le nouvel utilisateur\n";
        $authenticatedUser = $authService->authenticate('test@toubilib.com', 'testpassword123');
        echo "✓ Succès: Authentification réussie pour le nouvel utilisateur\n";
        
    } catch (Exception $e) {
        echo "✗ Erreur création/authentification: " . $e->getMessage() . "\n";
    }

    echo "\n=== Fin des tests d'intégration ===\n";

} catch (Exception $e) {
    echo "✗ Erreur fatale: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}