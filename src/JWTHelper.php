<?php

namespace App;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private string $key, $alg;

    public function __construct()
    {
        $this->key = $_ENV['JWT_SECRET'] ?? 'example_key';
        $this->alg = 'HS256';
    }

    public function encode(array $user, int $ttl, bool $access): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + $ttl,
            'jti' => bin2hex(random_bytes(16)),
            'sub' => $user['id'],
            'name' => $user['name'],
            'access' => $access,
        ];

        $jwt = JWT::encode($payload, $this->key, $this->alg);

        return $jwt;
    }

    public function decode(string $auth): array|string
    {
        try {
            $directives = explode(' ', $auth);
            $scheme = $directives[0];
            $jwt = $directives[1] ?? '';

            if ($scheme !== 'Bearer' || !$jwt) {
                throw new \Exception();
            }

            $decoded = JWT::decode($jwt, new Key($this->key, $this->alg));
            $decodedArray = (array)$decoded;

            return $decodedArray;
        } catch (ExpiredException $th) {
            return $th->getMessage();
        } catch (\Throwable $th) {
            return '';
        }
    }
}
