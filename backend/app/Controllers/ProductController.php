<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\BrandModel;
use App\Models\ReviewModel;

class ProductController extends BaseController
{
    private function formatProduct(array $prod): array
    {
        $fotoUrl = $prod['gambar'] ?? '';
        if (!empty($fotoUrl) && !str_starts_with($fotoUrl, 'http://') && !str_starts_with($fotoUrl, 'https://')) {
            $fotoUrl = base_url($fotoUrl);
        }
        
        $reviewModel = new ReviewModel();
        $avgRating = $reviewModel->where('id_produk', $prod['id_produk'])->selectAvg('rating')->first();
        $rating = isset($avgRating['rating']) ? (float)$avgRating['rating'] : 4.8;

        return [
            'id' => (int)$prod['id_produk'],
            'nama_produk' => $prod['nama_produk'],
            'brand' => $prod['nama_brand'] ?? 'Premium',
            'id_brand' => (int)($prod['id_brand'] ?? 0),
            'kategori' => $prod['nama_kategori'] ?? 'Laptop',
            'id_kategori' => (int)($prod['id_kategori'] ?? 0),
            'harga' => (float)$prod['harga'],
            'stok' => (int)$prod['stok'],
            'foto' => $fotoUrl,
            'rating' => $rating,
            'deskripsi' => $prod['deskripsi'] ?? '',
            'processor' => $prod['processor'] ?? '',
            'ram' => $prod['ram'] ?? '',
            'storage' => $prod['storage'] ?? '',
            'gpu' => $prod['gpu'] ?? '',
            'layar' => $prod['layar'] ?? '',
            'garansi' => $prod['garansi'] ?? '',
            'baterai' => $prod['baterai'] ?? '',
            'berat' => $prod['berat'] ?? '',
            'os' => $prod['os'] ?? '',
            'konektivitas' => $prod['konektivitas'] ?? '',
            'kamera' => $prod['kamera'] ?? '',
            'resolusi' => $prod['resolusi'] ?? '',
        ];
    }

    public function index()
    {
        $produkModel = new ProdukModel();
        
        $builder = $produkModel->db->table('produk')
            ->select('produk.*, brand.nama_brand, kategori.nama_kategori')
            ->join('brand', 'brand.id_brand = produk.id_brand', 'left')
            ->join('kategori', 'kategori.id_kategori = produk.id_kategori', 'left');

        $category  = $this->request->getGet('category');
        $brand     = $this->request->getGet('brand');
        $search    = $this->request->getGet('search');
        $minPrice  = $this->request->getGet('min_price');
        $maxPrice  = $this->request->getGet('max_price');
        $sort      = $this->request->getGet('sort');

        // Filter kategori: bisa ID angka atau nama string
        if (!empty($category)) {
            if (is_numeric($category)) {
                $builder->where('produk.id_kategori', (int)$category);
            } else {
                $builder->where('kategori.nama_kategori', $category);
            }
        }

        // Filter brand: bisa ID angka atau nama string
        if (!empty($brand)) {
            if (is_numeric($brand)) {
                $builder->where('produk.id_brand', (int)$brand);
            } else {
                $builder->where('brand.nama_brand', $brand);
            }
        }

        // Filter pencarian nama/deskripsi/brand
        if (!empty($search)) {
            $builder->groupStart()
                ->like('produk.nama_produk', $search)
                ->orLike('produk.deskripsi', $search)
                ->orLike('brand.nama_brand', $search)
                ->groupEnd();
        }

        // Filter harga minimum
        if (!empty($minPrice) && is_numeric($minPrice)) {
            $builder->where('produk.harga >=', (float)$minPrice);
        }

        // Filter harga maksimum
        if (!empty($maxPrice) && is_numeric($maxPrice)) {
            $builder->where('produk.harga <=', (float)$maxPrice);
        }

        // Sorting harga
        if ($sort === 'cheap') {
            $builder->orderBy('produk.harga', 'ASC');
        } elseif ($sort === 'expensive') {
            $builder->orderBy('produk.harga', 'DESC');
        } else {
            $builder->orderBy('produk.id_produk', 'ASC');
        }

        $products = $builder->get()->getResultArray();
        
        $formatted = [];
        foreach ($products as $prod) {
            $formatted[] = $this->formatProduct($prod);
        }

        return $this->respondWithSuccess("Daftar produk berhasil diambil", $formatted);
    }

    public function show($id)
    {
        $produkModel = new ProdukModel();
        $product = $produkModel->db->table('produk')
            ->select('produk.*, brand.nama_brand, kategori.nama_kategori')
            ->join('brand', 'brand.id_brand = produk.id_brand', 'left')
            ->join('kategori', 'kategori.id_kategori = produk.id_kategori', 'left')
            ->where('produk.id_produk', $id)
            ->get()->getRowArray();

        if (!$product) {
            return $this->respondWithError("Produk tidak ditemukan", 404);
        }

        $formatted = $this->formatProduct($product);
        return $this->respondWithSuccess("Detail produk berhasil diambil", $formatted);
    }

    public function reviews($id)
    {
        $reviewModel = new ReviewModel();
        $reviews = $reviewModel->db->table('review')
            ->select('review.*, pembeli.nama_pembeli, pembeli.foto_profil')
            ->join('pembeli', 'pembeli.id_pembeli = review.id_pembeli', 'left')
            ->where('review.id_produk', $id)
            ->get()->getResultArray();

        $formatted = [];
        foreach ($reviews as $rev) {
            $fotoUrl = $rev['foto_review'] ?? '';
            if (!empty($fotoUrl) && !str_starts_with($fotoUrl, 'http://') && !str_starts_with($fotoUrl, 'https://')) {
                $fotoUrl = base_url($fotoUrl);
            }
            $formatted[] = [
                'nama_pembeli' => $rev['nama_pembeli'] ?? 'Anonim',
                'rating' => (int)$rev['rating'],
                'komentar' => $rev['komentar'],
                'foto' => $fotoUrl,
                'created_at' => $rev['tanggal']
            ];
        }

        return $this->respondWithSuccess("Daftar ulasan produk", $formatted);
    }

    public function categories()
    {
        $kategoriModel = new KategoriModel();
        $categories = $kategoriModel->findAll();
        
        $formatted = [];
        foreach ($categories as $cat) {
            $imgUrl = $cat['gambar'] ?? '';
            if (!empty($imgUrl) && !str_starts_with($imgUrl, 'http://') && !str_starts_with($imgUrl, 'https://')) {
                $imgUrl = base_url($imgUrl);
            }
            $formatted[] = [
                'id' => (int)$cat['id_kategori'],
                'name' => $cat['nama_kategori'],
                'gambar' => $imgUrl,
                'deskripsi' => $cat['deskripsi']
            ];
        }

        return $this->respondWithSuccess("Daftar kategori berhasil diambil", $formatted);
    }
}
