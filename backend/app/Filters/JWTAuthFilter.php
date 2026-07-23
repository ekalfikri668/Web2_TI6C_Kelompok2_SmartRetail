<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class JWTAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything, but if it does,
     * it will abnormal termination of execution of the current request.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->header('Authorization');
        
        if (empty($authHeader)) {
            return $this->respondUnauthorized('Token otentikasi tidak ditemukan. Silakan masuk terlebih dahulu.');
        }

        // If the header object was returned
        if (is_object($authHeader)) {
            $authHeader = $authHeader->getValue();
        }

        // Parse Bearer Token
        $arr = explode(' ', $authHeader);
        $token = $arr[1] ?? null;

        if (empty($token)) {
            return $this->respondUnauthorized('Format token otentikasi tidak valid.');
        }

        helper('jwt_helper');
        $decoded = validateJWT($token);

        if (!$decoded) {
            return $this->respondUnauthorized('Sesi tidak valid atau telah berakhir. Silakan masuk kembali.');
        }

        // Inject user data into request for controllers to access
        $request->decodedToken = $decoded;
    }

    /**
     * Helper to return unauthorized JSON response.
     */
    private function respondUnauthorized(string $message): ResponseInterface
    {
        $response = Services::response();
        $response->setStatusCode(401);
        $response->setJSON([
            'status' => 401,
            'success' => false,
            'message' => $message
        ]);
        return $response;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
