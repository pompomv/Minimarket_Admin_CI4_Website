<?php

/**
 * UUID Helper
 *
 * Provides UUID v4 generation for use as VARCHAR(50) primary keys
 * in the customers, suppliers, and transactions tables.
 *
 * Usage:
 *   helper('uuid');
 *   $id = generate_uuid();          // returns e.g. "550e8400-e29b-41d4-a716-446655440000"
 *   $short = generate_short_id();   // returns a 12-char alphanumeric ID if UUID is overkill
 *
 * Load globally by adding 'uuid' to app/Config/Autoload.php $helpers array,
 * or call helper('uuid') inside any Controller / Model / Seeder.
 */

if (!function_exists('generate_uuid')) {
    /**
     * Generate a RFC 4122 version 4 UUID.
     *
     * @return string  e.g. "110e8400-e29b-41d4-a716-446655440000"
     */
    function generate_uuid(): string
    {
        // Use PHP 7.4+ random_bytes for cryptographically secure randomness
        $data = random_bytes(16);

        // Set version to 0100 (UUID v4)
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

        // Set bits 6-7 to 10 (variant)
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('generate_short_id')) {
    /**
     * Generate a compact 12-character alphanumeric ID.
     * Useful when you want a shorter VARCHAR key.
     *
     * @param  int    $length  Desired length (max 40)
     * @return string
     */
    function generate_short_id(int $length = 12): string
    {
        return substr(bin2hex(random_bytes(20)), 0, $length);
    }
}
