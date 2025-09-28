<?php
declare(strict_types=1);

namespace toubilib\api\exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpUnprocessableEntityException extends HttpSpecializedException
{
    protected $code = 422;
    protected $message = 'Unprocessable Entity';
}
