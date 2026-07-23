<?php

namespace App\Controllers;

use App\Models\KeranjangModel;
use App\Models\DetailKeranjangModel;
use App\Models\ProdukModel;

class CartController extends BaseController
{
    private function getOrCreateCart($pembeliId)
    {
        $keranjangModel = new KeranjangModel();
        $cart = $keranjangModel->where('id_pembeli', $pembeliId)->first();
        if (!$cart) {
            $cartId = $keranjangModel->insert([
                'id_pembeli' => $pembeliId,
                'tanggal' => date('Y-m-d H:i:s')
            ]);
            $cart = $keranjangModel->find($cartId);
        }
        return $cart;
    }

    public function index()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang memiliki keranjang", 403);
        }

        $cart = $this->getOrCreateCart($user->uid);
        
        $detailModel = new DetailKeranjangModel();
        $items = $detailModel->db->table('detail_keranjang')
            ->select('detail_keranjang.*, produk.nama_produk, produk.harga, produk.gambar, produk.stok, brand.nama_brand')
            ->join('produk', 'produk.id_produk = detail_keranjang.id_produk', 'left')
            ->join('brand', 'brand.id_brand = produk.id_brand', 'left')
            ->where('detail_keranjang.id_keranjang', $cart['id_keranjang'])
            ->get()->getResultArray();

        $totalPrice = 0;
        foreach ($items as $item) {
            $totalPrice += $item['subtotal'];
        }

        return $this->respondWithSuccess("Isi keranjang belanja", [
            'id_keranjang' => $cart['id_keranjang'],
            'items' => $items,
            'total_harga' => $totalPrice
        ]);
    }

    public function add()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa menambah produk ke keranjang", 403);
        }

        $productId = $this->request->getVar('id_produk') ?? $this->request->getVar('product_id');
        $qty = (int)$this->request->getVar('jumlah');
        $warna = $this->request->getVar('warna') ?? 'Standar';
        $tipe = $this->request->getVar('tipe') ?? 'Standar';

        if (empty($productId)) {
            return $this->respondWithError("Validasi gagal", 400, ['id_produk' => 'Field id_produk atau product_id wajib diisi']);
        }

        $rules = [
            'jumlah' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi gagal", 400, $this->validator->getErrors());
        }

        $produkModel = new ProdukModel();
        $product = $produkModel->find($productId);
        if (!$product) {
            return $this->respondWithError("Produk tidak ditemukan", 404);
        }

        if ($product['stok'] < $qty) {
            return $this->respondWithError("Stok tidak mencukupi. Sisa stok: " . $product['stok'], 400);
        }

        $cart = $this->getOrCreateCart($user->uid);
        $detailModel = new DetailKeranjangModel();

        // Check if item already exists in cart with same color and type
        $existing = $detailModel->where('id_keranjang', $cart['id_keranjang'])
                                 ->where('id_produk', $productId)
                                 ->where('warna', $warna)
                                 ->where('tipe', $tipe)
                                 ->first();

        if ($existing) {
            $newQty = $existing['jumlah'] + $qty;
            if ($product['stok'] < $newQty) {
                return $this->respondWithError("Stok tidak mencukupi untuk jumlah akumulasi. Sisa stok: " . $product['stok'], 400);
            }
            $subtotal = $product['harga'] * $newQty;
            $detailModel->update($existing['id_detail'], [
                'jumlah' => $newQty,
                'subtotal' => $subtotal
            ]);
            return $this->respondWithSuccess("Jumlah produk di keranjang diperbarui", null);
        } else {
            $subtotal = $product['harga'] * $qty;
            $detailModel->insert([
                'id_keranjang' => $cart['id_keranjang'],
                'id_produk'    => $productId,
                'warna'        => $warna,
                'tipe'         => $tipe,
                'jumlah'       => $qty,
                'subtotal'     => $subtotal
            ]);

            // Notify admin of new cart addition
            try {
                $notifAdminModel = new \App\Models\NotifikasiAdminModel();
                $pembeliModel    = new \App\Models\PembeliModel();
                $pembeli         = $pembeliModel->find($user->uid);
                $namaPembeli     = $pembeli['nama_pembeli'] ?? 'Pelanggan';
                $notifAdminModel->insert([
                    'judul'       => 'Produk Ditambahkan ke Keranjang',
                    'isi'         => $namaPembeli . ' menambahkan "' . $product['nama_produk'] . '" (x' . $qty . ') ke keranjang belanja.',
                    'tipe'        => 'keranjang',
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Fail silently
            }

            return $this->respondWithSuccess("Produk berhasil ditambahkan ke keranjang", null, 201);
        }
    }

    public function update($id)
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa mengupdate keranjang", 403);
        }

        $rules = [
            'jumlah' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi gagal", 400, $this->validator->getErrors());
        }

        $qty = (int)$this->request->getVar('jumlah');
        $cart = $this->getOrCreateCart($user->uid);

        $detailModel = new DetailKeranjangModel();
        $item = $detailModel->where('id_detail', $id)
                            ->where('id_keranjang', $cart['id_keranjang'])
                            ->first();

        if (!$item) {
            return $this->respondWithError("Item keranjang tidak ditemukan", 404);
        }

        $produkModel = new ProdukModel();
        $product = $produkModel->find($item['id_produk']);

        if ($product['stok'] < $qty) {
            return $this->respondWithError("Stok tidak mencukupi. Sisa stok: " . $product['stok'], 400);
        }

        $subtotal = $product['harga'] * $qty;
        $detailModel->update($id, [
            'jumlah' => $qty,
            'subtotal' => $subtotal
        ]);

        return $this->respondWithSuccess("Item keranjang berhasil diperbarui", null);
    }

    public function delete($id)
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa menghapus item keranjang", 403);
        }

        $cart = $this->getOrCreateCart($user->uid);
        $detailModel = new DetailKeranjangModel();
        
        $item = $detailModel->where('id_detail', $id)
                            ->where('id_keranjang', $cart['id_keranjang'])
                            ->first();

        if (!$item) {
            return $this->respondWithError("Item keranjang tidak ditemukan", 404);
        }

        $detailModel->delete($id);
        return $this->respondWithSuccess("Item berhasil dihapus dari keranjang", null);
    }
}
