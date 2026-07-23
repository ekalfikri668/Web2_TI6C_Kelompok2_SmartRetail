<?php

namespace App\Controllers;

use App\Models\NotifikasiModel;
use App\Models\NotifikasiAdminModel;

class NotificationController extends BaseController
{
    public function index()
    {
        $user = $this->request->decodedToken;

        if ($user->role === 'admin') {
            $notifModel = new NotifikasiAdminModel();
            $notifications = $notifModel->orderBy('tanggal', 'DESC')->findAll();
        } else {
            $notifModel = new NotifikasiModel();
            $notifications = $notifModel->where('id_pembeli', $user->uid)
                                        ->orderBy('tanggal', 'DESC')
                                        ->findAll();
        }

        // Format dates nicely
        foreach ($notifications as &$notif) {
            $notif['id'] = $notif['id_notifikasi']; // Ensure compatibility with frontend expecting 'id'
            $notif['tanggal_formatted'] = date('d M Y H:i', strtotime($notif['tanggal']));
        }

        return $this->respondWithSuccess("Daftar notifikasi berhasil diambil", $notifications);
    }

    public function markAsRead($id)
    {
        $user = $this->request->decodedToken;

        if ($user->role === 'admin') {
            $notifModel = new NotifikasiAdminModel();
            $notif = $notifModel->find($id);
            if (!$notif) {
                return $this->respondWithError("Notifikasi tidak ditemukan", 404);
            }
            $notifModel->update($id, ['status_baca' => 'sudah']);
        } else {
            $notifModel = new NotifikasiModel();
            $notif = $notifModel->find($id);
            if (!$notif) {
                return $this->respondWithError("Notifikasi tidak ditemukan", 404);
            }
            // Security check: Pembeli cannot read other pembeli's notifications
            if ((int)$notif['id_pembeli'] !== (int)$user->uid) {
                return $this->respondWithError("Akses ditolak", 403);
            }
            $notifModel->update($id, ['status_baca' => 'sudah']);
        }

        return $this->respondWithSuccess("Notifikasi berhasil ditandai sebagai dibaca");
    }

    public function markAllAsRead()
    {
        $user = $this->request->decodedToken;

        if ($user->role === 'admin') {
            $notifModel = new NotifikasiAdminModel();
            $notifModel->where('status_baca', 'belum')
                       ->set(['status_baca' => 'sudah'])
                       ->update();
        } else {
            $notifModel = new NotifikasiModel();
            $notifModel->where('id_pembeli', $user->uid)
                       ->where('status_baca', 'belum')
                       ->set(['status_baca' => 'sudah'])
                       ->update();
        }

        return $this->respondWithSuccess("Semua notifikasi berhasil ditandai sebagai dibaca");
    }
}
