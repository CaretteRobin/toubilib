<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\entities\user\UserRole;

echo "=== Création d'un administrateur ===\n\n";

try {
    // Bootstrap Slim app and container
    $app = require_once __DIR__ . '/../../config/bootstrap.php';
    $container = $app->getContainer();

    /** @var ServiceAuthInterface $authService */
    $authService = $container->get(ServiceAuthInterface::class);

    // Demander les informations
    echo "Email de l'administrateur: ";
    $email = trim(fgets(STDIN));
    
    echo "Mot de passe: ";
    $password = trim(fgets(STDIN));
    
    if (empty($email) || empty($password)) {
        echo "❌ Email et mot de passe requis\n";
        exit(1);
    }

    // Créer l'administrateur
    $adminUser = $authService->createUser($email, $password, UserRole::ADMIN);
    
    echo "\n✅ Administrateur créé avec succès!\n";
    echo "ID: {$adminUser->id}\n";
    echo "Email: {$adminUser->email}\n";
    echo "Rôle: " . UserRole::toString($adminUser->role) . "\n";
    
    // Tester l'authentification
    echo "\n=== Test d'authentification ===\n";
    $authenticatedUser = $authService->authenticate($email, $password);
    echo "✅ Authentification réussie!\n";
    
    // Générer un token JWT
    $token = $authService->generateJwtToken($authenticatedUser);
    echo "\n🔑 Token JWT généré:\n";
    echo substr($token, 0, 50) . "...\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    if ($e instanceof \toubilib\core\domain\exceptions\DuplicateUserException) {
        echo "💡 Cet email existe déjà dans la base de données.\n";
    }
    exit(1);
}