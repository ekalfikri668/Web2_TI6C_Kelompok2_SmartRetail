<?php

namespace App\Controllers;

use App\Models\PesananModel;
use App\Models\DetailPesananModel;
use App\Models\KeranjangModel;
use App\Models\DetailKeranjangModel;
use App\Models\ProdukModel;
use App\Models\AlamatModel;

class OrderController extends BaseController
{
    public function index()
    {
        $user = $this->request->decodedToken;
        $pesananModel = new PesananModel();
        $orders = $pesananModel->where('id_pembeli', $user->uid)
                               ->orderBy('tanggal_pesanan', 'DESC')
                               ->findAll();

        // Attach items to each order so frontend can show product names and images
        $detailModel = new DetailPesananModel();
        foreach ($orders as &$order) {
            $items = $detailModel->db->table('detail_pesanan')
                ->select('detail_pesanan.*, produk.nama_produk, produk.gambar')
                ->join('produk', 'produk.id_produk = detail_pesanan.id_produk', 'left')
                ->where('detail_pesanan.id_pesanan', $order['id_pesanan'])
                ->get()->getResultArray();

            // Make image URLs absolute
            foreach ($items as &$item) {
                if (!empty($item['gambar'])) {
                    $item['gambar'] = base_url($item['gambar']);
                }
            }
            unset($item);

            $order['items'] = $items;
        }
        unset($order);

        return $this->respondWithSuccess("Riwayat pesanan berhasil diambil", $orders);
    }

    public function show($id)
    {
        $user = $this->request->decodedToken;
        $pesananModel = new PesananModel();
        $order = $pesananModel->where('id_pesanan', $id)
                             ->where('id_pembeli', $user->uid)
                             ->first();

        if (!$order) {
            return $this->respondWithError("Pesanan tidak ditemukan", 404);
        }

        // Get address details
        $alamatModel = new AlamatModel();
        $alamat = $alamatModel->find($order['id_alamat']);
        $order['alamat'] = $alamat;

        // Get order items
        $detailModel = new DetailPesananModel();
        $items = $detailModel->db->table('detail_pesanan')
            ->select('detail_pesanan.*, produk.nama_produk, produk.gambar')
            ->join('produk', 'produk.id_produk = detail_pesanan.id_produk', 'left')
            ->where('detail_pesanan.id_pesanan', $id)
            ->get()->getResultArray();
        $order['items'] = $items;

        // Get payment info
        $payment = $pesananModel->db->table('pembayaran')
            ->where('id_pesanan', $id)
            ->get()->getRowArray();
        $order['pembayaran'] = $payment;

        // Get shipping info
        $shipping = $pesananModel->db->table('pengiriman')
            ->where('id_pesanan', $id)
            ->get()->getRowArray();
        $order['pengiriman'] = $shipping;

        return $this->respondWithSuccess("Rincian pesanan", $order);
    }

    public function create()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa melakukan checkout", 403);
        }

        $rules = [
            'id_alamat' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi checkout gagal", 400, $this->validator->getErrors());
        }

        $alamatId = $this->request->getVar('id_alamat');
        $alamatModel = new AlamatModel();
        $alamat = $alamatModel->where('id_alamat', $alamatId)
                             ->where('id_pembeli', $user->uid)
                             ->first();

        if (!$alamat) {
            return $this->respondWithError("Alamat pengiriman tidak valid", 400);
        }

        // Get Cart
        $keranjangModel = new KeranjangModel();
        $cart = $keranjangModel->where('id_pembeli', $user->uid)->first();
        if (!$cart) {
            return $this->respondWithError("Keranjang Anda kosong", 400);
        }

        $detailKeranjangModel = new DetailKeranjangModel();
        
        $cartItemIdsStr = $this->request->getVar('cart_item_ids');
        if (!empty($cartItemIdsStr)) {
            $cartItemIds = is_array($cartItemIdsStr) ? $cartItemIdsStr : explode(',', $cartItemIdsStr);
            $cartItems = $detailKeranjangModel->where('id_keranjang', $cart['id_keranjang'])
                                              ->whereIn('id_detail', $cartItemIds)
                                              ->findAll();
        } else {
            $cartItems = $detailKeranjangModel->where('id_keranjang', $cart['id_keranjang'])->findAll();
        }

        if (empty($cartItems)) {
            return $this->respondWithError("Keranjang Anda kosong", 400);
        }

        $produkModel = new ProdukModel();

        // 1. Verify stock first
        foreach ($cartItems as $item) {
            $product = $produkModel->find($item['id_produk']);
            if (!$product || $product['stok'] < $item['jumlah']) {
                return $this->respondWithError("Stok produk '" . ($product['nama_produk'] ?? 'Unknown') . "' tidak mencukupi.", 400);
            }
        }

        // 2. Perform transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        $totalHarga = 0;
        foreach ($cartItems as $item) {
            $totalHarga += $item['subtotal'];
        }

        $pesananModel = new PesananModel();
        $orderData = [
            'id_pembeli' => $user->uid,
            'id_alamat' => $alamatId,
            'tanggal_pesanan' => date('Y-m-d H:i:s'),
            'total_harga' => $totalHarga,
            'status_pesanan' => 'Menunggu Pembayaran'
        ];
        $orderId = $pesananModel->insert($orderData);

        $detailPesananModel = new DetailPesananModel();
        foreach ($cartItems as $item) {
            $product = $produkModel->find($item['id_produk']);
            $detailPesananModel->insert([
                'id_pesanan' => $orderId,
                'id_produk' => $item['id_produk'],
                'warna' => $item['warna'] ?? 'Standar',
                'tipe' => $item['tipe'] ?? 'Standar',
                'jumlah' => $item['jumlah'],
                'harga' => $product['harga'],
                'subtotal' => $item['subtotal']
            ]);

            // Deduct stock
            $produkModel->update($item['id_produk'], [
                'stok' => $product['stok'] - $item['jumlah']
            ]);
        }

        // Clear cart (only for the checked out items)
        if (!empty($cartItemIdsStr)) {
            $detailKeranjangModel->where('id_keranjang', $cart['id_keranjang'])
                                 ->whereIn('id_detail', $cartItemIds)
                                 ->delete();
        } else {
            $detailKeranjangModel->where('id_keranjang', $cart['id_keranjang'])->delete();
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->respondWithError("Gagal memproses pesanan. Silakan coba kembali.", 500);
        } else {
            $db->transCommit();
            
            // Insert Notifications
            try {
                $notifModel = new \App\Models\NotifikasiModel();
                
                // 1. Notification to Pembeli
                $notifModel->insert([
                    'id_pembeli'  => $user->uid,
                    'judul'       => 'Pesanan Dibuat',
                    'isi'         => 'Pesanan dengan ID #' . $orderId . ' berhasil dibuat. Silakan lakukan pembayaran senilai Rp ' . number_format($totalHarga, 0, ',', '.') . '.',
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);

                // 2. Notification to Admin (gunakan NotifikasiAdminModel -> tabel notifikasi_admin)
                $notifAdminModel = new \App\Models\NotifikasiAdminModel();
                $notifAdminModel->insert([
                    'judul'       => 'Pesanan Baru Masuk',
                    'isi'         => 'Pembeli ' . ($user->nama ?? 'Pelanggan') . ' telah melakukan checkout pesanan baru dengan ID #' . $orderId . ' senilai Rp ' . number_format($totalHarga, 0, ',', '.') . '.',
                    'tipe'        => 'pesanan',
                    'id_referensi'=> $orderId,
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Fail silently
            }

            return $this->respondWithSuccess("Pesanan berhasil dibuat", ['id_pesanan' => $orderId, 'status' => 'Menunggu Pembayaran'], 201);
        }
    }

    public function konfirmasiTiba($id)
    {
        $user = $this->request->decodedToken;
        $pesananModel = new PesananModel();
        $order = $pesananModel->where('id_pesanan', $id)
                             ->where('id_pembeli', $user->uid)
                             ->first();

        if (!$order) {
            return $this->respondWithError("Pesanan tidak ditemukan", 404);
        }

        // Change order status to 'Selesai'
        $pesananModel->update($id, ['status_pesanan' => 'Selesai']);

        // Update shipping status to 'Selesai'
        $pengirimanModel = new \App\Models\PengirimanModel();
        $pengirimanModel->where('id_pesanan', $id)
                       ->set(['status_pengiriman' => 'Selesai'])
                       ->update();

        // Add user notification
        try {
            $notifModel = new \App\Models\NotifikasiModel();
            $notifModel->insert([
                'id_pembeli'  => $user->uid,
                'judul'       => 'Pesanan Selesai',
                'isi'         => 'Terima kasih, Anda telah mengonfirmasi bahwa pesanan #' . $id . ' telah diterima.',
                'tipe'        => 'pesanan',
                'status_baca' => 'belum',
                'tanggal'     => date('Y-m-d H:i:s')
            ]);

            // Add admin notification
            $notifAdminModel = new \App\Models\NotifikasiAdminModel();
            $notifAdminModel->insert([
                'judul'       => 'Pesanan Diterima Pelanggan',
                'isi'         => 'Pelanggan telah mengonfirmasi penerimaan barang untuk pesanan #' . $id . '.',
                'tipe'        => 'pesanan',
                'id_referensi'=> $id,
                'status_baca' => 'belum',
                'tanggal'     => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Penerimaan pesanan berhasil dikonfirmasi");
    }
}
