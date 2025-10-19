<?php
declare(strict_types=1);

namespace toubilib\api\security;

class JwtPayload
{
    public string $subject;
    public string $email;
    public int $role;
    public string $type;
    public int $expiresAt;

    public function __construct(string $subject, string $email, int $role, string $type, int $expiresAt)
    {
        $this->subject = $subject;
        $this->email = $email;
        $this->role = $role;
        $this->type = $type;
        $this->expiresAt = $expiresAt;
    }
}
