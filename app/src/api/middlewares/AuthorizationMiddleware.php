<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\usecases\ServiceAuthorization;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ServiceAuthorization $authzService
    ) {}

    public function process(Request $request, Handler $handler): Response
    {
        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
        if ($user === null) {
            throw new HttpUnauthorizedException($request, 'Authentification requise.');
        }

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        if ($route === null) {
            throw new HttpForbiddenException($request, 'Route non trouvée.');
        }

        $routePattern = $route->getPattern();
        $routeArgs = $route->getArguments();

        // Vérifications spécifiques selon la route
        if (!$this->checkRouteAccess($user, $routePattern, $routeArgs, $request->getMethod())) {
            throw new HttpForbiddenException($request, 'Accès non autorisé à cette ressource.');
        }

        return $handler->handle($request);
    }

    private function checkRouteAccess(UserDTO $user, string $routePattern, array $args, string $method): bool
    {
        // Agenda d'un praticien : GET /praticiens/{id}/agenda
        if ($routePattern === '/praticiens/{id}/agenda' && $method === 'GET') {
            $practitionerId = $args['id'] ?? '';
            return $this->authzService->canAccessPractitionerAgenda($user, $practitionerId);
        }

        // Détail d'un rendez-vous : GET /rdv/{id}
        if ($routePattern === '/rdv/{id}' && $method === 'GET') {
            $appointmentId = $args['id'] ?? '';
            return $this->authzService->canAccessAppointmentDetails($user, $appointmentId);
        }

        // Modification d'un rendez-vous : PATCH/DELETE /rdv/{id}
        if ($routePattern === '/rdv/{id}' && in_array($method, ['PATCH', 'DELETE'], true)) {
            $appointmentId = $args['id'] ?? '';
            return $this->authzService->canModifyAppointment($user, $appointmentId);
        }

        // Création d'un rendez-vous : POST /rdv
        if ($routePattern === '/rdv' && $method === 'POST') {
            return $this->authzService->canCreateAppointment($user);
        }

        // Par défaut, autoriser (pour les routes non spécifiées)
        return true;
    }
}