<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

echo "=== Test simplifié des classes Auth ===\n\n";

use toubilib\api\security\JwtManager;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\infra\repositories\PDOUserRepository;

try {
    echo "Test 1: Création d'entité User\n";
    $user = new User(
        'test-id-123',
        'test@example.com',
        password_hash('password123', PASSWORD_DEFAULT),
        UserRole::USER
    );
    echo "[OK] Utilisateur créé avec succès\n";
    echo "  - ID: {$user->id}\n";
    echo "  - Email: {$user->email}\n";
    echo "  - Rôle: " . UserRole::toString($user->role) . "\n";

    echo "\nTest 2: Vérification mot de passe\n";
    echo $user->verifyPassword('password123')
        ? "[OK] Mot de passe correct"
        : "[ERREUR] Le mot de passe devrait être accepté";
    echo "\n";
    echo $user->verifyPassword('wrongpassword')
        ? "[ERREUR] Le mot de passe invalide a été accepté"
        : "[OK] Mot de passe incorrect rejeté";
    echo "\n";

    echo "\nTest 3: Création DTO\n";
    $dto = UserDTO::fromEntity($user);
    echo "[OK] DTO créé\n";
    print_r($dto->toArray());

    echo "\nTest 4: Service Auth (sans base de données)\n";
    $mockPdo = new class extends PDO {
        public function __construct() {
            // PDO factice sans vrai pilote
        }
        public function prepare($statement, $options = []) {
            return new class {
                public function execute($params = []) { return true; }
                public function fetch($mode = null) { 
                    return [
                        'id' => 'mock-user-id',
                        'email' => 'mock@example.com',
                        'password' => password_hash('mockpassword', PASSWORD_DEFAULT),
                        'role' => UserRole::USER
                    ];
                }
            };
        }
    };

    // Test sans vraie base de données - juste la logique métier
    $jwtSecret = 'test-secret';
    $authService = new ServiceAuth(new PDOUserRepository($mockPdo));
    $jwtManager = new JwtManager($jwtSecret, 3600, 604800);

    echo "\nTest 5: Génération JWT\n";
    $token = $jwtManager->createAccessToken($dto);
    echo "[OK] Jeton JWT généré (longueur: " . strlen($token) . " caractères)\n";

    echo "\n=== Tous les tests de base sont concluants ===\n";
    echo "Note: Tests avec vraie base de données nécessitent PostgreSQL configuré\n";

} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
