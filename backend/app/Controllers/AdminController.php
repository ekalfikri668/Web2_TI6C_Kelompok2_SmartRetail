<?php

namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\BrandModel;
use App\Models\PembeliModel;
use App\Models\PesananModel;
use App\Models\DetailPesananModel;
use App\Models\PembayaranModel;
use App\Models\PengirimanModel;
use App\Models\ReviewModel;

class AdminController extends BaseController
{
    private function checkAdmin()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'admin') {
            throw new \Exception("Akses ditolak. Area khusus Admin.", 403);
        }
    }

    public function dashboard()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $produkModel = new ProdukModel();
        $pembeliModel = new PembeliModel();
        $pesananModel = new PesananModel();

        $totalProduk = $produkModel->countAll();
        $totalCustomers = $pembeliModel->countAll();
        $totalOrders = $pesananModel->countAll();

        // Total Revenue
        $revenueRow = $pesananModel->selectSum('total_harga')
                                   ->where('status_pesanan !=', 'Dibatalkan')
                                   ->first();
        $totalRevenue = (float)($revenueRow['total_harga'] ?? 0);

        // Chart Data (monthly sales)
        $chartData = $pesananModel->db->table('pesanan')
            ->select("DATE_FORMAT(tanggal_pesanan, '%Y-%m') as bulan, SUM(total_harga) as total")
            ->where('status_pesanan !=', 'Dibatalkan')
            ->groupBy("bulan")
            ->orderBy("bulan", "ASC")
            ->limit(12)
            ->get()->getResultArray();

        // Best selling product
        $bestSellerRow = $pesananModel->db->table('detail_pesanan')
            ->select('produk.nama_produk, SUM(detail_pesanan.jumlah) as total_qty')
            ->join('produk', 'produk.id_produk = detail_pesanan.id_produk')
            ->join('pesanan', 'pesanan.id_pesanan = detail_pesanan.id_pesanan')
            ->where('pesanan.status_pesanan !=', 'Dibatalkan')
            ->groupBy('detail_pesanan.id_produk')
            ->orderBy('total_qty', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
        
        $produkTerlaris = $bestSellerRow['nama_produk'] ?? 'Tidak ada data';

        // Prepare sales chart structure
        $labels = [];
        $data = [];
        foreach ($chartData as $row) {
            $labels[] = date('M Y', strtotime($row['bulan'] . '-01'));
            $data[] = (float)$row['total'];
        }
        if (empty($labels)) {
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
            $data = [0, 0, 0, 0, 0, 0];
        }
        $salesChart = [
            'labels' => $labels,
            'data' => $data
        ];

        return $this->respondWithSuccess("Dashboard stats", [
            'total_produk' => $totalProduk,
            'total_pelanggan' => $totalCustomers,
            'total_pesanan' => $totalOrders,
            'total_pendapatan' => $totalRevenue,
            'produk_terlaris' => $produkTerlaris,
            'sales_chart' => $salesChart
        ]);
    }

    // CRUD Products
    public function products()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $produkModel = new ProdukModel();
        $products = $produkModel->db->table('produk')
            ->select('produk.*, brand.nama_brand, kategori.nama_kategori')
            ->join('brand', 'brand.id_brand = produk.id_brand', 'left')
            ->join('kategori', 'kategori.id_kategori = produk.id_kategori', 'left')
            ->get()->getResultArray();

        // Format to match frontend fields
        $db = \Config\Database::connect();
        $formatted = [];
        foreach ($products as $p) {
            $prodId = $p['id_produk'];

            // Fetch warna stok
            $warnaStok = $db->table('produk_warna_stok')
                ->where('id_produk', $prodId)->get()->getResultArray();

            // Fetch additional photos
            $fotoTambahan = $db->table('produk_foto')
                ->where('id_produk', $prodId)
                ->orderBy('urutan', 'ASC')
                ->get()->getResultArray();
            $fotoUrls = array_map(fn($f) => base_url($f['foto']), $fotoTambahan);

            $formatted[] = array_merge($p, [
                'id'           => $prodId,
                'brand'        => $p['nama_brand'] ?? '',
                'kategori'     => $p['nama_kategori'] ?? '',
                'foto'         => $p['gambar'] ? base_url($p['gambar']) : '',
                'warna_stok'   => $warnaStok,
                'foto_tambahan'=> $fotoUrls,
            ]);
        }

        return $this->respondWithSuccess("Daftar produk admin", $formatted);
    }

    public function createProduct()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $namaProduk = trim($this->request->getVar('nama_produk') ?? '');
        if (empty($namaProduk)) {
            return $this->respondWithError("Nama produk wajib diisi", 400);
        }

        // Resolve brand: accept id_brand (int) or brand name string
        $brandModel = new BrandModel();
        $idBrand = $this->request->getVar('id_brand');
        if (empty($idBrand)) {
            $brandName = trim($this->request->getVar('brand') ?? '');
            $brandRow = $brandModel->where('nama_brand', $brandName)->first();
            if (!$brandRow) {
                // Auto-create brand if not exists
                $idBrand = $brandModel->insert(['nama_brand' => $brandName ?: 'Unknown'], true);
            } else {
                $idBrand = $brandRow['id_brand'];
            }
        }

        // Resolve kategori: accept id_kategori (int) or category name string
        $kategoriModel = new KategoriModel();
        $idKategori = $this->request->getVar('id_kategori');
        if (empty($idKategori)) {
            $katName = trim($this->request->getVar('kategori') ?? '');
            $katRow = $kategoriModel->where('nama_kategori', $katName)->first();
            if (!$katRow) {
                // Auto-create kategori if not exists
                $idKategori = $kategoriModel->insert(['nama_kategori' => $katName ?: 'Lainnya'], true);
            } else {
                $idKategori = $katRow['id_kategori'];
            }
        }

        $insertData = [
            'id_brand'    => $idBrand,
            'id_kategori' => $idKategori,
            'nama_produk' => $namaProduk,
            'harga'       => $this->request->getVar('harga') ?? 0,
            'stok'        => $this->request->getVar('stok') ?? 0,
            'deskripsi'   => $this->request->getVar('deskripsi') ?? '',
            'processor'   => $this->request->getVar('processor') ?? '',
            'ram'         => $this->request->getVar('ram') ?? '',
            'storage'     => $this->request->getVar('storage') ?? '',
            'gpu'         => $this->request->getVar('gpu') ?? '',
            'layar'       => $this->request->getVar('layar') ?? '',
            'garansi'     => $this->request->getVar('garansi') ?? '',
            'baterai'     => $this->request->getVar('baterai') ?? '',
            'berat'       => $this->request->getVar('berat') ?? '',
            'os'          => $this->request->getVar('os') ?? '',
            'konektivitas'=> $this->request->getVar('konektivitas') ?? '',
            'kamera'      => $this->request->getVar('kamera') ?? '',
            'resolusi'    => $this->request->getVar('resolusi') ?? '',
        ];

        // Handle file upload (foto is optional)
        $file = $this->request->getFile('foto') ?? $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/products/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $insertData['gambar'] = 'uploads/products/' . $newName;
        }

        $produkModel = new ProdukModel();
        $newProductId = $produkModel->insert($insertData, true);

        // Handle multiple additional photos
        $fotoTambahan = $this->request->getFiles('foto_tambahan');
        if (!empty($fotoTambahan['foto_tambahan'])) {
            $db = \Config\Database::connect();
            $uploadPath = FCPATH . 'uploads/products/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            foreach ($fotoTambahan['foto_tambahan'] as $extraFile) {
                if ($extraFile->isValid() && !$extraFile->hasMoved()) {
                    $extraName = $extraFile->getRandomName();
                    $extraFile->move($uploadPath, $extraName);
                    $db->table('produk_foto')->insert([
                        'id_produk' => $newProductId,
                        'foto'      => 'uploads/products/' . $extraName
                    ]);
                }
            }
        }

        // Handle warna & stok per warna
        $warnaNama = $this->request->getVar('warna_nama');
        $warnaStok = $this->request->getVar('warna_stok');
        if (!empty($warnaNama) && is_array($warnaNama)) {
            $db = \Config\Database::connect();
            foreach ($warnaNama as $idx => $warna) {
                $warna = trim($warna);
                if (!empty($warna)) {
                    $db->table('produk_warna_stok')->insert([
                        'id_produk' => $newProductId,
                        'warna'     => $warna,
                        'stok'      => (int)($warnaStok[$idx] ?? 0)
                    ]);
                }
            }
        }

        return $this->respondWithSuccess("Produk berhasil ditambahkan", null, 201);
    }

    public function updateProduct($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $produkModel = new ProdukModel();
        $product = $produkModel->find($id);
        if (!$product) {
            return $this->respondWithError("Produk tidak ditemukan", 404);
        }

        // Resolve brand
        $brandModel = new BrandModel();
        $idBrand = $this->request->getVar('id_brand');
        if (empty($idBrand)) {
            $brandName = trim($this->request->getVar('brand') ?? '');
            $brandRow = $brandModel->where('nama_brand', $brandName)->first();
            $idBrand = $brandRow ? $brandRow['id_brand'] : $product['id_brand'];
        }

        // Resolve kategori
        $kategoriModel = new KategoriModel();
        $idKategori = $this->request->getVar('id_kategori');
        if (empty($idKategori)) {
            $katName = trim($this->request->getVar('kategori') ?? '');
            $katRow = $kategoriModel->where('nama_kategori', $katName)->first();
            $idKategori = $katRow ? $katRow['id_kategori'] : $product['id_kategori'];
        }

        $data = [
            'id_brand'    => $idBrand,
            'id_kategori' => $idKategori,
            'nama_produk' => $this->request->getVar('nama_produk') ?? $product['nama_produk'],
            'harga'       => $this->request->getVar('harga') ?? $product['harga'],
            'stok'        => $this->request->getVar('stok') ?? $product['stok'],
            'deskripsi'   => $this->request->getVar('deskripsi') ?? $product['deskripsi'],
            'processor'   => $this->request->getVar('processor') ?? '',
            'ram'         => $this->request->getVar('ram') ?? '',
            'storage'     => $this->request->getVar('storage') ?? '',
            'gpu'         => $this->request->getVar('gpu') ?? '',
            'layar'       => $this->request->getVar('layar') ?? '',
            'garansi'     => $this->request->getVar('garansi') ?? '',
            'baterai'     => $this->request->getVar('baterai') ?? '',
            'berat'       => $this->request->getVar('berat') ?? '',
            'os'          => $this->request->getVar('os') ?? '',
            'konektivitas'=> $this->request->getVar('konektivitas') ?? '',
            'kamera'      => $this->request->getVar('kamera') ?? '',
            'resolusi'    => $this->request->getVar('resolusi') ?? '',
        ];

        // File upload: accept 'foto' or 'gambar' field name
        $file = $this->request->getFile('foto') ?? $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/products/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            if (!empty($product['gambar'])) {
                $oldFile = FCPATH . $product['gambar'];
                if (is_file($oldFile)) unlink($oldFile);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $data['gambar'] = 'uploads/products/' . $newName;
        }

        $produkModel->update($id, $data);

        // Handle extra photos
        $fotoTambahan = $this->request->getFiles('foto_tambahan');
        if (!empty($fotoTambahan['foto_tambahan'])) {
            $db = \Config\Database::connect();
            $uploadPath = FCPATH . 'uploads/products/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            foreach ($fotoTambahan['foto_tambahan'] as $extraFile) {
                if ($extraFile->isValid() && !$extraFile->hasMoved()) {
                    $extraName = $extraFile->getRandomName();
                    $extraFile->move($uploadPath, $extraName);
                    $db->table('produk_foto')->insert([
                        'id_produk' => $id,
                        'foto'      => 'uploads/products/' . $extraName
                    ]);
                }
            }
        }

        // Handle warna & stok: replace all
        $warnaNama = $this->request->getVar('warna_nama');
        $warnaStok = $this->request->getVar('warna_stok');
        if (!empty($warnaNama) && is_array($warnaNama)) {
            $db = \Config\Database::connect();
            $db->table('produk_warna_stok')->where('id_produk', $id)->delete();
            foreach ($warnaNama as $idx => $warna) {
                $warna = trim($warna);
                if (!empty($warna)) {
                    $db->table('produk_warna_stok')->insert([
                        'id_produk' => $id,
                        'warna'     => $warna,
                        'stok'      => (int)($warnaStok[$idx] ?? 0)
                    ]);
                }
            }
        }

        return $this->respondWithSuccess("Produk berhasil diperbarui", null);
    }

    public function deleteProduct($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $produkModel = new ProdukModel();
        $product = $produkModel->find($id);
        if (!$product) {
            return $this->respondWithError("Produk tidak ditemukan", 404);
        }

        if (!empty($product['gambar'])) {
            $oldFile = FCPATH . $product['gambar'];
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $produkModel->delete($id);
        return $this->respondWithSuccess("Produk berhasil dihapus", null);
    }

    // CRUD Categories
    public function categories()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $kategoriModel = new KategoriModel();
        return $this->respondWithSuccess("Daftar kategori admin", $kategoriModel->findAll());
    }

    public function createCategory()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $namaKategori = trim($this->request->getVar('nama_kategori') ?? '');
        if (empty($namaKategori)) {
            return $this->respondWithError("Nama kategori wajib diisi", 400);
        }

        $insertData = [
            'nama_kategori' => $namaKategori,
            'deskripsi'     => $this->request->getVar('deskripsi') ?? ''
        ];

        // Gambar is optional
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/categories/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $insertData['gambar'] = 'uploads/categories/' . $newName;
        }

        $kategoriModel = new KategoriModel();
        $kategoriModel->insert($insertData);

        return $this->respondWithSuccess("Kategori berhasil dibuat", null, 201);
    }

    public function updateCategory($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $kategoriModel = new KategoriModel();
        $category = $kategoriModel->find($id);
        if (!$category) {
            return $this->respondWithError("Kategori tidak ditemukan", 404);
        }

        // Support JSON body (PUT from api.php) and form-data
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $namaKategori = trim($input['nama_kategori'] ?? $this->request->getVar('nama_kategori') ?? '');
        if (empty($namaKategori) || strlen($namaKategori) < 3) {
            return $this->respondWithError("Nama kategori minimal 3 karakter", 400);
        }

        $data = [
            'nama_kategori' => $namaKategori,
            'deskripsi'     => $input['deskripsi'] ?? $this->request->getVar('deskripsi') ?? ''
        ];

        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/categories/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            if (!empty($category['gambar'])) {
                $oldFile = FCPATH . $category['gambar'];
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $data['gambar'] = 'uploads/categories/' . $newName;
        }

        $kategoriModel->update($id, $data);
        return $this->respondWithSuccess("Kategori berhasil diperbarui", null);
    }

    public function deleteCategory($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $kategoriModel = new KategoriModel();
        $category = $kategoriModel->find($id);
        if (!$category) {
            return $this->respondWithError("Kategori tidak ditemukan", 404);
        }

        if (!empty($category['gambar'])) {
            $oldFile = FCPATH . $category['gambar'];
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $kategoriModel->delete($id);
        return $this->respondWithSuccess("Kategori berhasil dihapus", null);
    }

    // CRUD Brands
    public function brands()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $brandModel = new BrandModel();
        return $this->respondWithSuccess("Daftar brand admin", $brandModel->findAll());
    }

    public function createBrand()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $namaBrand = trim($this->request->getVar('nama_brand') ?? '');
        if (empty($namaBrand)) {
            return $this->respondWithError("Nama brand wajib diisi", 400);
        }

        $insertData = [
            'nama_brand' => $namaBrand,
            'deskripsi'  => $this->request->getVar('deskripsi') ?? ''
        ];

        // Logo is optional
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/brands/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $insertData['logo'] = 'uploads/brands/' . $newName;
        }

        $brandModel = new BrandModel();
        $brandModel->insert($insertData);

        return $this->respondWithSuccess("Brand berhasil dibuat", null, 201);
    }

    public function updateBrand($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $brandModel = new BrandModel();
        $brand = $brandModel->find($id);
        if (!$brand) {
            return $this->respondWithError("Brand tidak ditemukan", 404);
        }

        // Support JSON body (PUT from api.php) and form-data
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $namaBrand = trim($input['nama_brand'] ?? $this->request->getVar('nama_brand') ?? '');
        if (empty($namaBrand) || strlen($namaBrand) < 2) {
            return $this->respondWithError("Nama brand minimal 2 karakter", 400);
        }

        $data = [
            'nama_brand' => $namaBrand,
            'deskripsi'  => $input['deskripsi'] ?? $this->request->getVar('deskripsi') ?? ''
        ];

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/brands/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            if (!empty($brand['logo'])) {
                $oldFile = FCPATH . $brand['logo'];
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $data['logo'] = 'uploads/brands/' . $newName;
        }

        $brandModel->update($id, $data);
        return $this->respondWithSuccess("Brand berhasil diperbarui", null);
    }

    public function deleteBrand($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $brandModel = new BrandModel();
        $brand = $brandModel->find($id);
        if (!$brand) {
            return $this->respondWithError("Brand tidak ditemukan", 404);
        }

        if (!empty($brand['logo'])) {
            $oldFile = FCPATH . $brand['logo'];
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $brandModel->delete($id);
        return $this->respondWithSuccess("Brand berhasil dihapus", null);
    }

    public function customers()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $db = \Config\Database::connect();
        $customers = $db->table('pembeli')
            ->select('pembeli.*, 
                (SELECT COUNT(*) FROM pesanan WHERE id_pembeli = pembeli.id_pembeli AND status_pesanan != "Dibatalkan") as total_pesanan,
                (SELECT IFNULL(SUM(total_harga), 0) FROM pesanan WHERE id_pembeli = pembeli.id_pembeli AND status_pesanan != "Dibatalkan") as total_belanja')
            ->get()->getResultArray();

        return $this->respondWithSuccess("Daftar pelanggan", $customers);
    }

    public function updateCustomerStatus($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $status = $input['status'] ?? 'aktif';
        if (!in_array($status, ['aktif', 'nonaktif'])) {
            return $this->respondWithError("Status tidak valid. Gunakan 'aktif' atau 'nonaktif'", 400);
        }

        $pembeliModel = new PembeliModel();
        $customer = $pembeliModel->find($id);
        if (!$customer) {
            return $this->respondWithError("Pelanggan tidak ditemukan", 404);
        }

        $pembeliModel->update($id, ['status' => $status]);
        return $this->respondWithSuccess("Status pelanggan berhasil diubah menjadi " . $status, null);
    }

    public function deleteCustomer($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $pembeliModel = new PembeliModel();
        $customer = $pembeliModel->find($id);
        if (!$customer) {
            return $this->respondWithError("Pelanggan tidak ditemukan", 404);
        }

        $pembeliModel->delete($id);
        return $this->respondWithSuccess("Pelanggan berhasil dihapus", null);
    }

    // Payments verification
    public function payments()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $pembayaranModel = new PembayaranModel();
        $payments = $pembayaranModel->db->table('pembayaran')
            ->select('pembayaran.*, pesanan.total_harga, pembeli.nama_pembeli')
            ->join('pesanan', 'pesanan.id_pesanan = pembayaran.id_pesanan', 'left')
            ->join('pembeli', 'pembeli.id_pembeli = pesanan.id_pembeli', 'left')
            ->orderBy('pembayaran.tanggal_bayar', 'DESC')
            ->get()->getResultArray();

        return $this->respondWithSuccess("Daftar pembayaran", $payments);
    }

    public function verifyPayment($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $rules = [
            'status' => 'required|in_list[Disetujui,Ditolak]'
        ];

        // Read from JSON body (PUT sends JSON) or form POST
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }
        $status = $input['status'] ?? $this->request->getVar('status');

        if (!in_array($status, ['Disetujui', 'Ditolak'])) {
            return $this->respondWithError("Status tidak valid. Gunakan 'Disetujui' atau 'Ditolak'", 400);
        }

        $pembayaranModel = new PembayaranModel();
        $payment = $pembayaranModel->find($id);

        if (!$payment) {
            return $this->respondWithError("Pembayaran tidak ditemukan", 404);
        }

        $pembayaranModel->update($id, [
            'status' => $status
        ]);

        $orderStatus = ($status === 'Disetujui') ? 'Diproses' : 'Dibatalkan';
        $pesananModel = new PesananModel();
        $pesananModel->update($payment['id_pesanan'], [
            'status_pesanan' => $orderStatus
        ]);

        // Insert notification to pembeli
        try {
            $order = $pesananModel->find($payment['id_pesanan']);
            if ($order) {
                $notifModel = new \App\Models\NotifikasiModel();
                
                $judul = ($status === 'Disetujui') ? 'Pembayaran Diterima' : 'Pembayaran Ditolak';
                $isi = ($status === 'Disetujui') 
                    ? 'Pembayaran untuk pesanan ID #' . $payment['id_pesanan'] . ' telah disetujui. Pesanan Anda sedang diproses.'
                    : 'Pembayaran untuk pesanan ID #' . $payment['id_pesanan'] . ' ditolak. Silakan unggah bukti pembayaran yang valid.';

                $notifModel->insert([
                    'id_pembeli'  => $order['id_pembeli'],
                    'judul'       => $judul,
                    'isi'         => $isi,
                    'tipe'        => 'pembayaran',
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Pembayaran telah diverifikasi (" . $status . ")", null);
    }

    public function shippingList()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $pesananModel = new PesananModel();
        $shipping = $pesananModel->db->table('pesanan')
            ->select('pesanan.id_pesanan, pesanan.total_harga, pesanan.status_pesanan,
                      pembeli.nama_pembeli, pembeli.email,
                      pengiriman.id_pengiriman, pengiriman.ekspedisi, pengiriman.nomor_resi,
                      pengiriman.status_pengiriman, pengiriman.estimasi_tiba')
            ->join('pembeli', 'pembeli.id_pembeli = pesanan.id_pembeli', 'left')
            ->join('pengiriman', 'pengiriman.id_pesanan = pesanan.id_pesanan', 'left')
            ->whereIn('pesanan.status_pesanan', ['Diproses', 'Dikirim', 'Selesai'])
            ->orderBy('pesanan.id_pesanan', 'DESC')
            ->get()->getResultArray();

        // FORMAT ULANG agar sesuai dengan frontend
        $formattedShipping = [];
        foreach ($shipping as $ship) {
            $formattedShipping[] = [
                'id'                 => $ship['id_pengiriman'] ?? null,
                'id_pesanan'         => $ship['id_pesanan'],
                'order_id'           => 'ORD-' . str_pad($ship['id_pesanan'], 4, '0', STR_PAD_LEFT),
                'customer'           => $ship['nama_pembeli'] ?? 'Tidak diketahui',
                'email'              => $ship['email'] ?? '',
                'ekspedisi'          => $ship['ekspedisi'] ?? 'Belum ditentukan',
                'nomor_resi'         => $ship['nomor_resi'] ?? 'Menunggu input',
                'status'             => $ship['status_pengiriman'] ?? $ship['status_pesanan'] ?? 'Diproses',
                'status_pengiriman'  => $ship['status_pengiriman'] ?? $ship['status_pesanan'] ?? 'Diproses',
                'estimasi'           => $ship['estimasi_tiba'] ? date('d M Y', strtotime($ship['estimasi_tiba'])) : '-',
                'estimasi_tiba'      => $ship['estimasi_tiba'],
                'total_harga'        => (float)($ship['total_harga'] ?? 0),
                'sudah_dikirim'      => !empty($ship['nomor_resi']) && $ship['nomor_resi'] !== 'Menunggu input'
            ];
        }

        return $this->respondWithSuccess("Daftar pengiriman berhasil diambil", $formattedShipping);
    }

    /**
     * Shipping Management - Buat atau update pengiriman
     */
    public function createShipping()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        // Support both JSON and form-data
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $rules = [
            'id_pesanan'     => 'required|integer',
            'ekspedisi'      => 'required|max_length[100]',
            'nomor_resi'     => 'required|max_length[100]',
            'estimasi_tiba'  => 'required|valid_date'
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        
        if (!$validation->run($input)) {
            return $this->respondWithError("Validasi pengiriman gagal", 400, $validation->getErrors());
        }

        $pesananId = $input['id_pesanan'];
        $pesananModel = new PesananModel();
        $order = $pesananModel->find($pesananId);

        if (!$order) {
            return $this->respondWithError("Pesanan tidak ditemukan", 404);
        }

        $pengirimanModel = new PengirimanModel();
        
        // Cek apakah sudah ada pengiriman untuk pesanan ini
        $existing = $pengirimanModel->where('id_pesanan', $pesananId)->first();
        
        if ($existing) {
            // Update existing shipping
            $pengirimanModel->update($existing['id_pengiriman'], [
                'ekspedisi' => $input['ekspedisi'],
                'nomor_resi' => $input['nomor_resi'],
                'status_pengiriman' => 'Dikirim',
                'estimasi_tiba' => $input['estimasi_tiba']
            ]);
        } else {
            // Create new shipping
            $shippingData = [
                'id_pesanan' => $pesananId,
                'ekspedisi' => $input['ekspedisi'],
                'nomor_resi' => $input['nomor_resi'],
                'status_pengiriman' => 'Dikirim',
                'estimasi_tiba' => $input['estimasi_tiba']
            ];
            $pengirimanModel->insert($shippingData);
        }

        // Update order status to Dikirim
        $pesananModel->update($pesananId, [
            'status_pesanan' => 'Dikirim'
        ]);

        // Insert notification to pembeli
        try {
            $notifModel = new \App\Models\NotifikasiModel();
            $notifModel->insert([
                'id_pembeli'  => $order['id_pembeli'],
                'judul'       => 'Pesanan Dikirim',
                'isi'         => 'Pesanan Anda dengan ID #' . $pesananId . ' telah dikirim melalui ' . 
                         $input['ekspedisi'] . ' dengan nomor resi ' . 
                         $input['nomor_resi'] . '. Estimasi tiba: ' . 
                         date('d M Y', strtotime($input['estimasi_tiba'])),
                'tipe'        => 'pengiriman',
                'status_baca' => 'belum',
                'tanggal'     => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Pengiriman berhasil didaftarkan", null, 201);
    }

    /**
     * Orders Management - Daftar semua pesanan (FORMATTED untuk frontend)
     */
    public function orders()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $pesananModel = new PesananModel();
        $orders = $pesananModel->db->table('pesanan')
            ->select('pesanan.*, pembeli.nama_pembeli, pembeli.email, pembeli.no_hp')
            ->join('pembeli', 'pembeli.id_pembeli = pesanan.id_pembeli', 'left')
            ->orderBy('pesanan.tanggal_pesanan', 'DESC')
            ->get()->getResultArray();

        // FORMAT ULANG agar sesuai dengan frontend
        $formattedOrders = [];
        foreach ($orders as $order) {
            $formattedOrders[] = [
                'id_pesanan' => $order['id_pesanan'],
                'order_id' => 'ORD-' . str_pad($order['id_pesanan'], 4, '0', STR_PAD_LEFT),
                'customer' => $order['nama_pembeli'] ?? 'Tidak diketahui',
                'nama_pembeli' => $order['nama_pembeli'] ?? 'Tidak diketahui',
                'email' => $order['email'] ?? '',
                'no_hp' => $order['no_hp'] ?? '',
                'tanggal' => $order['tanggal_pesanan'],
                'tanggal_pesanan' => $order['tanggal_pesanan'],
                'total' => (float)$order['total_harga'],
                'total_harga' => (float)$order['total_harga'],
                'status' => $order['status_pesanan'] ?? 'Menunggu Pembayaran',
                'status_pesanan' => $order['status_pesanan'] ?? 'Menunggu Pembayaran',
                'id_alamat' => $order['id_alamat'] ?? null
            ];
        }

        return $this->respondWithSuccess("Daftar semua pesanan berhasil diambil", $formattedOrders);
    }

    /**
     * Orders Management - Update status pesanan
     */
    public function updateOrder($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        // Support both PUT and POST with _method
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        // Map status -> status_pesanan for validation
        if (isset($input['status']) && !isset($input['status_pesanan'])) {
            $input['status_pesanan'] = $input['status'];
        }

        $rules = [
            'status_pesanan' => 'required|max_length[50]'
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        
        if (!$validation->run($input)) {
            return $this->respondWithError("Validasi status pesanan gagal", 400, $validation->getErrors());
        }

        $pesananModel = new PesananModel();
        $order = $pesananModel->find($id);
        if (!$order) {
            return $this->respondWithError("Pesanan tidak ditemukan", 404);
        }

        $status = $input['status_pesanan'] ?? $this->request->getVar('status_pesanan');
        
        // Validasi status yang diizinkan
        $allowedStatus = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
        if (!in_array($status, $allowedStatus)) {
            return $this->respondWithError("Status pesanan tidak valid", 400);
        }

        $pesananModel->update($id, [
            'status_pesanan' => $status
        ]);

        // Jika status berubah menjadi 'Selesai', update status pengiriman juga
        if ($status === 'Selesai') {
            $pengirimanModel = new PengirimanModel();
            $pengirimanModel->where('id_pesanan', $id)
                           ->set(['status_pengiriman' => 'Selesai'])
                           ->update();
        }

        // Insert notification to pembeli
        try {
            $notifModel = new \App\Models\NotifikasiModel();
            $notifModel->insert([
                'id_pembeli' => $order['id_pembeli'],
                'judul' => 'Status Pesanan Diperbarui',
                'isi' => 'Status pesanan Anda dengan ID #' . $id . ' telah diperbarui menjadi "' . $status . '".',
                'tipe' => 'pesanan',
                'status_baca' => 'belum',
                'tanggal' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Status pesanan berhasil diperbarui menjadi (" . $status . ")", null);
    }

    // Reports Management
    public function reports()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $pesananModel = new PesananModel();
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $pesananModel->db->table('pesanan')
            ->select('pesanan.*, pembeli.nama_pembeli')
            ->join('pembeli', 'pembeli.id_pembeli = pesanan.id_pembeli', 'left')
            ->where('pesanan.status_pesanan', 'Selesai');

        if (!empty($startDate)) {
            $builder->where('DATE(pesanan.tanggal_pesanan) >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder->where('DATE(pesanan.tanggal_pesanan) <=', $endDate);
        }

        $orders = $builder->orderBy('pesanan.tanggal_pesanan', 'DESC')->get()->getResultArray();
        return $this->respondWithSuccess("Laporan penjualan berhasil diambil", $orders);
    }

    // Reviews
    public function reviews()
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        $reviewModel = new ReviewModel();
        $reviews = $reviewModel->db->table('review')
            ->select('review.*, pembeli.nama_pembeli, produk.nama_produk')
            ->join('pembeli', 'pembeli.id_pembeli = review.id_pembeli', 'left')
            ->join('produk', 'produk.id_produk = review.id_produk', 'left')
            ->orderBy('review.tanggal', 'DESC')
            ->get()->getResultArray();

        return $this->respondWithSuccess("Daftar ulasan", $reviews);
    }

    public function replyReview($id)
    {
        try {
            $this->checkAdmin();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), $e->getCode());
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $balasan = trim($input['balasan_admin'] ?? '');
        if (empty($balasan)) {
            return $this->respondWithError("Balasan tidak boleh kosong", 400);
        }

        $reviewModel = new ReviewModel();
        $review = $reviewModel->find($id);
        if (!$review) {
            return $this->respondWithError("Ulasan tidak ditemukan", 404);
        }

        $reviewModel->update($id, ['balasan_admin' => $balasan]);

        // Notify the user
        try {
            $notifModel = new \App\Models\NotifikasiModel();
            $notifModel->insert([
                'id_pembeli'  => $review['id_pembeli'],
                'judul'       => 'Admin Membalas Ulasan Anda',
                'isi'         => 'Admin telah membalas ulasan produk Anda: "' . substr($balasan, 0, 80) . '"',
                'tipe'        => 'ulasan',
                'status_baca' => 'belum',
                'tanggal' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Balasan berhasil dikirim", null);
    }
}