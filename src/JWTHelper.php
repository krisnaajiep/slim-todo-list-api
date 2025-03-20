<?php

namespace App;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * The JWTHelper class.
 * 
 * This class handles JWT token operations.
 */
class JWTHelper
{
    /**
     * The JWT key.
     * 
     * @var string
     */
    private string $key;

    /**
     * The JWT algorithm.
     * 
     * @var string
     */
    private string $alg;

    /**
     * Creates a new JWTHelper instance.
     */
    public function __construct()
    {
        $this->key = $_ENV['JWT_SECRET'] ?? 'example_key';
        $this->alg = 'HS256';
    }

    /**
     * Encodes a user data into a JWT token.
     * 
     * @param array $user The user data.
     * @param int $ttl The token time to live.
     * @param bool $access The access type.
     * 
     * @return string The JWT token.
     */
    public function encode(array $user, int $ttl, bool $access): string
    {
        // Create the payload.
        $payload = [
            'iat' => time(),
            'exp' => time() + $ttl,
            'jti' => bin2hex(random_bytes(16)),
            'sub' => $user['id'],
            'name' => $user['name'],
            'access' => $access,
        ];

        // Encode the payload.
        $jwt = JWT::encode($payload, $this->key, $this->alg);

        return $jwt;
    }

    /**
     * Decodes a JWT token into a user data.
     * 
     * @param string $auth The JWT token.
     * 
     * @return array|string The user data.
     */
    public function decode(string $jwt): array|string
    {
        try {
            // Decode the JWT token.
            $decoded = JWT::decode($jwt, new Key($this->key, $this->alg));

            // Convert the decoded object to an array.
            $decodedArray = (array)$decoded;

            return $decodedArray;
        } catch (ExpiredException $th) {
            // The token has expired.
            return $th->getMessage();
        } catch (\Throwable $th) {
            // The token is invalid.
            return '';
        }
    }
}
