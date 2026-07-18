<?php

namespace App\Models;

class CustomerOtp
{
    private ?int $id;
    private string $email;
    private string $otp;
    private string $expiresAt;
    private bool $isUsed;

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->email = $data['email'];
        $this->otp = $data['otp'];
        $this->expiresAt = $data['expires_at'];
        $this->isUsed = (bool)($data['is_used'] ?? false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getOtp(): string
    {
        return $this->otp;
    }

    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }
}
