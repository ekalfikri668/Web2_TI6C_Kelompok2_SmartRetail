<?php

use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Get JWT secret key from env.
 */
function getJWTSecretKey(): string
{
    return env('JWT_SECRET') ?? 'fallback_secret_key_default';
}

/**
 * Generate JWT token for a user.
 */
function generateJWT(array $userData): string
{
    $issuedAtTime = time();
    $tokenDuration = (int)(env('JWT_EXPIRE') ?? 86400); // 1 day default
    $expirationTime = $issuedAtTime + $tokenDuration;
    
    $payload = [
        'iat' => $issuedAtTime,
        'exp' => $expirationTime,
        'uid' => $userData['id'] ?? null,
        'email' => $userData['email'] ?? null,
        'role' => $userData['role'] ?? null,
        'nama' => $userData['nama'] ?? null
    ];
    
    return JWT::encode($payload, getJWTSecretKey(), 'HS256');
}

/**
 * Validate and decode JWT token from request header.
 */
function validateJWT(string $token): ?object
{
    try {
        $decoded = JWT::decode($token, new Key(getJWTSecretKey(), 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}
