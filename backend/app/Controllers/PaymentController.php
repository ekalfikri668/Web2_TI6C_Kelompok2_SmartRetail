<?php

namespace App\Controllers;

use App\Models\PembayaranModel;
use App\Models\PesananModel;

class PaymentController extends BaseController
{
    public function create()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa melakukan pembayaran", 403);
        }

        $metode = $this->request->getVar('metode');

        $rules = [
            'id_pesanan'   => 'required|integer',
            'metode'       => 'required|max_length[50]',
            'jumlah_bayar' => 'required|numeric',
        ];

        if ($metode !== 'QRIS') {
            $rules['bukti_bayar'] = 'uploaded[bukti_bayar]|max_size[bukti_bayar,2048]|is_image[bukti_bayar]';
        }

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi pembayaran gagal", 400, $this->validator->getErrors());
        }

        $orderId = $this->request->getVar('id_pesanan');
        $pesananModel = new PesananModel();
        $order = $pesananModel->where('id_pesanan', $orderId)
                             ->where('id_pembeli', $user->uid)
                             ->first();

        if (!$order) {
            return $this->respondWithError("Pesanan tidak ditemukan", 404);
        }

        $buktiBayarPath = 'uploads/payments/qris-auto-verified.png';

        if ($metode !== 'QRIS') {
            // Handle File Upload
            $file = $this->request->getFile('bukti_bayar');
            if (!$file->isValid() || $file->hasMoved()) {
                return $this->respondWithError("File upload bukti pembayaran tidak valid", 400);
            }

            $uploadPath = FCPATH . 'uploads/payments/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $buktiBayarPath = 'uploads/payments/' . $newName;
        }

        $paymentModel = new PembayaranModel();
        
        // Remove existing payment if any
        $paymentModel->where('id_pesanan', $orderId)->delete();

        $paymentStatus = ($metode === 'QRIS') ? 'Lunas' : 'Menunggu Verifikasi';
        $orderStatus = ($metode === 'QRIS') ? 'Diproses' : 'Menunggu Verifikasi Pembayaran';

        $paymentData = [
            'id_pesanan' => $orderId,
            'metode' => $metode,
            'jumlah_bayar' => $this->request->getVar('jumlah_bayar'),
            'bukti_bayar' => $buktiBayarPath,
            'tanggal_bayar' => date('Y-m-d H:i:s'),
            'status' => $paymentStatus
        ];

        $paymentModel->insert($paymentData);

        // Update order status
        $pesananModel->update($orderId, [
            'status_pesanan' => $orderStatus
        ]);

        // Insert Notifications
        try {
            $notifModel = new \App\Models\NotifikasiModel();
            
            // 1. Notification to Pembeli
            $notifModel->insert([
                'id_pembeli'  => $user->uid,
                'judul'       => 'Pembayaran Dikirim',
                'isi'         => 'Bukti pembayaran untuk pesanan ID #' . $orderId . ' telah diunggah. Menunggu verifikasi dari admin.',
                'status_baca' => 'belum',
                'tanggal'     => date('Y-m-d H:i:s')
            ]);

            // 2. Notification to Admin (gunakan NotifikasiAdminModel)
            $notifAdminModel = new \App\Models\NotifikasiAdminModel();
            $notifAdminModel->insert([
                'judul'        => 'Bukti Pembayaran Masuk',
                'isi'          => 'Pembeli ' . ($user->nama ?? 'Pelanggan') . ' telah mengunggah bukti pembayaran untuk pesanan ID #' . $orderId . '.',
                'tipe'         => 'pembayaran',
                'id_referensi' => $orderId,
                'status_baca'  => 'belum',
                'tanggal'      => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Bukti pembayaran berhasil diunggah", [
            'status' => 'Menunggu Verifikasi Pembayaran'
        ]);
    }
}
