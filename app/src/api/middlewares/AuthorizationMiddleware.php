<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;
use Throwable;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\usecases\AuthorizationServiceInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_RDV = 'rdv.dto';

    private AuthorizationServiceInterface $authorizationService;

    public function __construct(AuthorizationServiceInterface $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        if ($route === null) {
            throw new HttpInternalServerErrorException($request, 'Route introuvable pour le contrôle d\'autorisation.');
        }

        $name = $route->getName();
        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
        if ($user === null) {
            throw new HttpInternalServerErrorException($request, 'Utilisateur non trouvé pour le contrôle d\'autorisation.');
        }

        try {
            switch ($name) {
                case 'praticiens.agenda':
                    $praticienId = $route->getArgument('id');
                    $this->authorizationService->assertCanAccessAgenda($user, $praticienId);
                    break;
                case 'rdv.detail':
                    $rdvId = $route->getArgument('id');
                    $rdv = $this->authorizationService->assertCanViewRdv($user, $rdvId);
                    $request = $request->withAttribute(self::ATTRIBUTE_RDV, $rdv);
                    break;
                default:
                    // Pas de contrôle spécifique pour cette route
                    break;
            }
        } catch (AuthorizationException $exception) {
            throw new HttpForbiddenException($request, $exception->getMessage(), $exception);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Erreur lors du contrôle d\'autorisation.', $exception);
        }

        return $handler->handle($request);
    }
}
