<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    /**
     * The default CORS configuration.
     *
     * @var array{
     *      allowedOrigins: list<string>,
     *      allowedOriginsPatterns: list<string>,
     *      supportsCredentials: bool,
     *      allowedHeaders: list<string>,
     *      exposedHeaders: list<string>,
     *      allowedMethods: list<string>,
     *      maxAge: int,
     *  }
     */
    public array $default = [
        /**
         * Origins for the `Access-Control-Allow-Origin` header.
         * '*' mengizinkan semua origin — cocok untuk Flutter mobile yang pakai IP lokal
         */
        'allowedOrigins' => ['*'],

        'allowedOriginsPatterns' => [],

        'supportsCredentials' => false,

        /**
         * Header yang diizinkan — termasuk Authorization untuk JWT Bearer token
         */
        'allowedHeaders' => ['Authorization', 'Content-Type', 'Accept', 'X-Requested-With'],

        'exposedHeaders' => [],

        /**
         * Method HTTP yang diizinkan untuk API
         */
        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

        'maxAge' => 7200,
    ];
}
