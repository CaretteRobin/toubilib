<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use toubilib\api\security\JwtManagerInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\domain\entities\user\UserRole;

echo "=== Création d'un administrateur ===\n\n";

try {
    // Initialisation de l'application Slim et du conteneur
    $app = require_once __DIR__ . '/../../config/bootstrap.php';
    $container = $app->getContainer();

    /** @var ServiceAuthInterface $authService */
    $authService = $container->get(ServiceAuthInterface::class);
    /** @var JwtManagerInterface $jwtManager */
    $jwtManager = $container->get(JwtManagerInterface::class);

    // Demander les informations
    echo "Email de l'administrateur: ";
    $email = trim(fgets(STDIN));
    
    echo "Mot de passe: ";
    $password = trim(fgets(STDIN));
    
    if (empty($email) || empty($password)) {
        echo "[ERREUR] Email et mot de passe requis\n";
        exit(1);
    }

    // Créer l'administrateur
    $adminUser = $authService->createUser($email, $password, UserRole::ADMIN);
    
    echo "\n[SUCCES] Administrateur créé avec succès !\n";
    echo "ID: {$adminUser->id}\n";
    echo "Email: {$adminUser->email}\n";
    echo "Rôle: " . UserRole::toString($adminUser->role) . "\n";
    
    // Tester l'authentification
    echo "\n=== Test d'authentification ===\n";
    $authenticatedUser = $authService->authenticate($email, $password);
    echo "[SUCCES] Authentification réussie !\n";
    
    // Générer un token JWT
    $token = $jwtManager->createAccessToken($authenticatedUser);
    echo "\n[TOKEN] Jeton JWT généré :\n";
    echo substr($token, 0, 50) . "...\n";

} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
    if ($e instanceof \toubilib\core\domain\exceptions\DuplicateUserException) {
        echo "[INFO] Cet email existe déjà dans la base de données.\n";
    }
    exit(1);
}
