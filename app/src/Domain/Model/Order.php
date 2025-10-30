<?php

namespace App\Domain\Model;

final class Order
{
    public function __construct(
        private readonly int $id,
        private readonly string $hash,
        private readonly int $status,
        private readonly ?string $email,
        private readonly string $name,
        private readonly \DateTimeInterface $createDate,
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getHash(): string { return $this->hash; }
    public function getStatus(): int { return $this->status; }
    public function getEmail(): ?string { return $this->email; }
    public function getName(): string { return $this->name; }
    public function getCreateDate(): \DateTimeInterface { return $this->createDate; }
}
