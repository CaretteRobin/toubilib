<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\domain\entities\user\UserRole;

class ServiceAuthorization
{
    public function __construct(
        private RdvRepositoryInterface $rdvRepository
    ) {}

    /**
     * Vérifie si un utilisateur peut accéder à l'agenda d'un praticien
     */
    public function canAccessPractitionerAgenda(UserDTO $user, string $practitionerId): bool
    {
        // Admin peut accéder à tous les agendas
        if (UserRole::toString($user->role) === 'admin') {
            return true;
        }

        // Praticien peut accéder seulement à son propre agenda
        if (UserRole::toString($user->role) === 'praticien' && $user->id === $practitionerId) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut accéder au détail d'un rendez-vous
     */
    public function canAccessAppointmentDetails(UserDTO $user, string $appointmentId): bool
    {
        try {
            // Admin peut accéder à tous les rendez-vous
            if (UserRole::toString($user->role) === 'admin') {
                return true;
            }

            // Récupérer le rendez-vous pour vérifier les droits
            $rdv = $this->rdvRepository->getRdvById($appointmentId);

            // Praticien peut voir ses propres rendez-vous
            if (UserRole::toString($user->role) === 'praticien' && $rdv->praticien_id === $user->id) {
                return true;
            }

            // Patient peut voir ses propres rendez-vous
            if (UserRole::toString($user->role) === 'patient' && $rdv->patient_id === $user->id) {
                return true;
            }

            return false;
        } catch (ResourceNotFoundException $e) {
            // Si le rendez-vous n'existe pas, refuser l'accès
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur peut modifier un rendez-vous
     */
    public function canModifyAppointment(UserDTO $user, string $appointmentId): bool
    {
        try {
            // Admin peut modifier tous les rendez-vous
            if (UserRole::toString($user->role) === 'admin') {
                return true;
            }

            // Récupérer le rendez-vous pour vérifier les droits
            $rdv = $this->rdvRepository->getRdvById($appointmentId);

            // Seul le praticien du rendez-vous peut le modifier
            if (UserRole::toString($user->role) === 'praticien' && $rdv->praticien_id === $user->id) {
                return true;
            }

            return false;
        } catch (ResourceNotFoundException $e) {
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur peut créer un rendez-vous
     */
    public function canCreateAppointment(UserDTO $user): bool
    {
        $roleName = UserRole::toString($user->role);
        return in_array($roleName, ['admin', 'praticien'], true);
    }
}