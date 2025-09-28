<?php
declare(strict_types=1);

namespace toubilib\api\exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpConflictException extends HttpSpecializedException
{
    protected $code = 409;
    protected $message = 'Conflict';
}
