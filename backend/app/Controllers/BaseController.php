<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    use \CodeIgniter\API\ResponseTrait;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['url', 'jwt_helper'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        // Dynamically verify and migrate database fields if not present
        try {
            $db = \Config\Database::connect();
            
            // Check detail_keranjang fields
            if (!$db->fieldExists('warna', 'detail_keranjang')) {
                $db->query("ALTER TABLE detail_keranjang ADD COLUMN warna VARCHAR(50) DEFAULT NULL AFTER id_produk");
            }
            if (!$db->fieldExists('tipe', 'detail_keranjang')) {
                $db->query("ALTER TABLE detail_keranjang ADD COLUMN tipe VARCHAR(100) DEFAULT NULL AFTER warna");
            }

            // Check detail_pesanan fields
            if (!$db->fieldExists('warna', 'detail_pesanan')) {
                $db->query("ALTER TABLE detail_pesanan ADD COLUMN warna VARCHAR(50) DEFAULT NULL AFTER id_produk");
            }
            if (!$db->fieldExists('tipe', 'detail_pesanan')) {
                $db->query("ALTER TABLE detail_pesanan ADD COLUMN tipe VARCHAR(100) DEFAULT NULL AFTER warna");
            }
        } catch (\Exception $e) {
            log_message('error', 'Auto-migration error: ' . $e->getMessage());
        }
    }

    /**
     * Standard success JSON response.
     */
    protected function respondWithSuccess(string $message, $data = [], int $statusCode = 200): ResponseInterface
    {
        return $this->respond([
            'status' => $statusCode,
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Standard error JSON response.
     */
    protected function respondWithError(string $message, int $statusCode = 400, $errors = null): ResponseInterface
    {
        $response = [
            'status' => $statusCode,
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $statusCode);
    }
}

