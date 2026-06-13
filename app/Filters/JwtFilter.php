<?php

namespace App\Filters;

use App\Libraries\JwtHelper;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle preflight OPTIONS request (CORS)
        if ($request->getMethod() === 'options') {
            $response = service('response');
            $response->setHeader('Access-Control-Allow-Origin', '*');
            $response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setStatusCode(200);
            return $response;
        }

        $token = JwtHelper::getTokenFromHeader();

        if (!$token) {
            return service('response')
                ->setStatusCode(401)
                ->setHeader('Content-Type', 'application/json')
                ->setBody(json_encode([
                    'status'  => 'error',
                    'message' => 'Token not found. Please log in first.',
                ]));
        }

        $decoded = JwtHelper::validate($token);

        if (!$decoded) {
            return service('response')
                ->setStatusCode(401)
                ->setHeader('Content-Type', 'application/json')
                ->setBody(json_encode([
                    'status'  => 'error',
                    'message' => 'Token is invalid or has expired. Please log in again.',
                ]));
        }

        // Attach the decoded user data to the request for use in controllers
        $request->jwtPayload = $decoded;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add CORS headers to all API responses
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
