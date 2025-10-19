<?php

namespace toubilib\core\domain\exceptions;

class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Identifiants invalides.');
    }
}
