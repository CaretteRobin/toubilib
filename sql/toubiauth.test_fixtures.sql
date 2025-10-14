-- Script de mise à jour des fixtures utilisateurs avec mots de passe connus
-- À exécuter pour remplacer les mots de passe hashés existants par des mots de passe de test

-- Mise à jour des utilisateurs existants avec le mot de passe "password123"
UPDATE users SET password = '$2y$12$LQv3c.ovu.CCHR1QR9HYJOuQG/QvgqEjRhPsq7J5CPNDJaLVVhpGG' 
WHERE email IN (
    'Denis.Teixeira@hotmail.fr',
    'Marie.Guichard@sfr.fr',
    'Claude.Langlois@hotmail.fr'
);

-- Insertion d'un administrateur de test
INSERT INTO users (id, email, password, role) VALUES 
('admin-test-id-123', 'admin@toubilib.com', '$2y$12$LQv3c.ovu.CCHR1QR9HYJOuQG/QvgqEjRhPsq7J5CPNDJaLVVhpGG', 0)
ON CONFLICT (email) DO UPDATE SET 
    password = EXCLUDED.password,
    role = EXCLUDED.role;

-- Insertion d'un utilisateur de test
INSERT INTO users (id, email, password, role) VALUES 
('user-test-id-456', 'user@toubilib.com', '$2y$12$LQv3c.ovu.CCHR1QR9HYJOuQG/QvgqEjRhPsq7J5CPNDJaLVVhpGG', 1)
ON CONFLICT (email) DO UPDATE SET 
    password = EXCLUDED.password,
    role = EXCLUDED.role;

-- Le mot de passe pour tous ces comptes est : "password123"
-- Hash généré avec : password_hash('password123', PASSWORD_DEFAULT)