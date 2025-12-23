<?php
declare(strict_types=1);

namespace TestApp\Service;

class UsersService
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function ensureExists(array $data): array
    {
        return ['id' => 123, 'email' => $data['email'] ?? 'test@example.com'];
    }
}
