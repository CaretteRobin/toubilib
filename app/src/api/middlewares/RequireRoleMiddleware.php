<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\user\UserRole;

class RequireRoleMiddleware implements MiddlewareInterface
{
    /** @var string[] */
    private array $allowedRoles;

    /**
     * @param string[] $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = array_map('strtolower', $allowedRoles);
    }

    public function process(Request $request, Handler $handler): Response
    {
        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
        if ($user === null) {
            throw new HttpUnauthorizedException($request, 'Authentification requise.');
        }

        $roleName = strtolower(UserRole::toString($user->role));
        if (!in_array($roleName, $this->allowedRoles, true)) {
            throw new HttpForbiddenException($request, 'Accès refusé pour ce rôle.');
        }

        return $handler->handle($request);
    }
}
