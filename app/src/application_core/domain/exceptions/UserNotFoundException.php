<?php

namespace toubilib\core\domain\exceptions;

class UserNotFoundException extends \Exception
{
    public function __construct(string $identifier, string $type = 'ID')
    {
        parent::__construct("Utilisateur introuvable pour {$type} : {$identifier}");
    }
}
