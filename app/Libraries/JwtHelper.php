<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtHelper
{
    private static string $secret = 'minimarket_jwt_secret_key_2025_capstone';
    private static string $algo   = 'HS256';
    private static int    $ttl    = 60 * 60 * 8; // 8 hours

    /**
     * Generate a JWT token for a successfully authenticated user
     */
    public static function generate(array $payload): string
    {
        $now = time();
        $data = [
            'iat'      => $now,
            'exp'      => $now + self::$ttl,
            'user_id'  => $payload['user_id'],
            'username' => $payload['username'],
            'role'     => $payload['role'],
        ];

        return JWT::encode($data, self::$secret, self::$algo);
    }

    /**
     * Validate a JWT token from the Authorization header.
     * Returns the decoded payload or null if invalid.
     */
    public static function validate(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key(self::$secret, self::$algo));
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract the token string from the Authorization: Bearer <token> header
     */
    public static function getTokenFromHeader(): ?string
    {
        $header = service('request')->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
