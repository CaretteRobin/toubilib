<?php

namespace toubilib\core\domain\exceptions;

class DuplicateUserException extends \Exception
{
    public function __construct(string $email)
    {
        parent::__construct("Un utilisateur avec l'email \"{$email}\" existe déjà.");
    }
}
