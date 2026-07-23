<?php

namespace App\Controllers;

use App\Models\ChatModel;
use App\Models\PembeliModel;

class ChatController extends BaseController
{
    public function index()
    {
        $user = $this->request->decodedToken;
        $chatModel = new ChatModel();

        if ($user->role === 'admin') {
            $pembeliId = $this->request->getGet('id_pembeli');
            if (empty($pembeliId)) {
                return $this->respondWithError("id_pembeli wajib dikirim oleh admin", 400);
            }
        } else {
            $pembeliId = $user->uid;
        }

        $messages = $chatModel->where('id_pembeli', $pembeliId)
                             ->orderBy('waktu', 'ASC')
                             ->findAll();

        // Format for frontend response compatibility
        $formatted = [];
        foreach ($messages as $msg) {
            $isDeleted = (int)($msg['is_deleted'] ?? 0) === 1;
            $pesanText = $isDeleted ? 'Pesan ini telah dihapus' : ($msg['pesan'] ? $msg['pesan'] : ($msg['gambar'] ? base_url($msg['gambar']) : ''));
            $formatted[] = [
                'id' => $msg['id_chat'],
                'pengirim' => $msg['pengirim'], // 'user' or 'admin'
                'pesan' => $pesanText,
                'created_at' => date('H:i', strtotime($msg['waktu'])),
                'tipe' => ($msg['gambar'] && !$isDeleted) ? 'image' : 'text',
                'is_edited' => (int)($msg['is_edited'] ?? 0),
                'is_deleted' => $isDeleted ? 1 : 0
            ];
        }

        return $this->respondWithSuccess("Riwayat chat berhasil diambil", $formatted);
    }

    public function send()
    {
        $user = $this->request->decodedToken;
        $chatModel = new ChatModel();

        $rules = [
            'pesan' => 'permit_empty',
            'gambar' => 'permit_empty|max_size[gambar,2048]|is_image[gambar]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi chat gagal", 400, $this->validator->getErrors());
        }

        $pembeliId = null;
        $adminId = null;
        $pengirim = '';

        if ($user->role === 'admin') {
            $pembeliId = $this->request->getPost('id_pembeli');
            $adminId = $user->uid;
            $pengirim = 'admin';

            if (empty($pembeliId)) {
                return $this->respondWithError("id_pembeli wajib diisi oleh admin", 400);
            }
        } else {
            $pembeliId = $user->uid;
            $adminId = null; // Sent to customer service
            $pengirim = 'user';
        }

        $pesanText = $this->request->getPost('pesan');
        $gambarPath = null;

        // Check image file upload
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/chats/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            $gambarPath = 'uploads/chats/' . $newName;
        }

        if (empty($pesanText) && empty($gambarPath)) {
            return $this->respondWithError("Pesan atau gambar harus diisi", 400);
        }

        $chatData = [
            'id_pembeli' => $pembeliId,
            'id_admin' => $adminId,
            'pesan' => $pesanText,
            'gambar' => $gambarPath,
            'pengirim' => $pengirim,
            'waktu' => date('Y-m-d H:i:s')
        ];

        $chatModel->insert($chatData);

        // Insert notification for Chat
        try {
            if ($pengirim === 'user') {
                // Notify Admin via NotifikasiAdminModel
                $notifAdminModel = new \App\Models\NotifikasiAdminModel();
                $notifAdminModel->insert([
                    'judul'       => 'Pesan Baru Masuk',
                    'isi'         => 'Anda mendapatkan pesan baru dari ' . ($user->nama ?? 'Pembeli') . '.',
                    'tipe'        => 'chat',
                    'id_referensi'=> $pembeliId,
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);
            } else {
                // Notify User via NotifikasiModel
                $notifModel = new \App\Models\NotifikasiModel();
                $notifModel->insert([
                    'id_pembeli'  => $pembeliId,
                    'judul'       => 'Pesan dari Admin',
                    'isi'         => 'Customer service telah membalas chat Anda.',
                    'status_baca' => 'belum',
                    'tanggal'     => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return $this->respondWithSuccess("Pesan berhasil dikirim", [
            'pengirim' => $pengirim,
            'pesan' => $pesanText ? $pesanText : base_url($gambarPath),
            'tipe' => $gambarPath ? 'image' : 'text',
            'created_at' => date('H:i')
        ]);
    }

    /**
     * Admin: Get list of all users who have chatted, with last message preview
     */
    public function adminUserList()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'admin') {
            return $this->respondWithError("Akses ditolak", 403);
        }

        $chatModel = new ChatModel();
        $pembeliModel = new PembeliModel();

        // Get distinct pembeli IDs that have chats
        $chatData = $chatModel->db->table('chat')
            ->select('chat.id_pembeli, MAX(chat.waktu) as last_time')
            ->groupBy('chat.id_pembeli')
            ->orderBy('last_time', 'DESC')
            ->get()->getResultArray();

        $result = [];
        foreach ($chatData as $row) {
            $pembeli = $pembeliModel->find($row['id_pembeli']);
            if (!$pembeli) continue;

            // Get last message
            $lastMsg = $chatModel->where('id_pembeli', $row['id_pembeli'])
                                  ->orderBy('waktu', 'DESC')
                                  ->first();

            // Count unread user messages
            // We count all messages from 'user' as a simple proxy for unread
            // (avoid dependency on is_read column which may not exist)
            try {
                $unread = $chatModel->where('id_pembeli', $row['id_pembeli'])
                                     ->where('pengirim', 'user')
                                     ->countAllResults();
            } catch (\Exception $e) {
                $unread = 0;
            }

            $result[] = [
                'id_pembeli'   => $row['id_pembeli'],
                'nama_pembeli' => $pembeli['nama_pembeli'] ?? 'Pengguna',
                'email'        => $pembeli['email'] ?? '',
                'last_message' => $lastMsg['pesan'] ?? ($lastMsg['gambar'] ? '[Gambar]' : ''),
                'last_time'    => $row['last_time'],
                'unread_count' => (int)$unread,
            ];
        }

        return $this->respondWithSuccess("Daftar pengguna chat berhasil diambil", $result);
    }

    public function edit($id)
    {
        $user = $this->request->decodedToken;
        $chatModel = new ChatModel();
        $chat = $chatModel->find($id);

        if (!$chat) {
            return $this->respondWithError("Pesan tidak ditemukan", 404);
        }

        // Security check
        if ($user->role === 'admin') {
            if ($chat['pengirim'] !== 'admin' || (int)$chat['id_admin'] !== (int)$user->uid) {
                return $this->respondWithError("Akses ditolak", 403);
            }
        } else {
            if ($chat['pengirim'] !== 'user' || (int)$chat['id_pembeli'] !== (int)$user->uid) {
                return $this->respondWithError("Akses ditolak", 403);
            }
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        $input = $input ?? $this->request->getPost();
        $pesan = $input['pesan'] ?? '';

        if (empty($pesan)) {
            return $this->respondWithError("Pesan tidak boleh kosong", 400);
        }

        $chatModel->update($id, [
            'pesan' => $pesan,
            'is_edited' => 1
        ]);

        return $this->respondWithSuccess("Pesan berhasil diperbarui");
    }

    public function delete($id)
    {
        $user = $this->request->decodedToken;
        $chatModel = new ChatModel();
        $chat = $chatModel->find($id);

        if (!$chat) {
            return $this->respondWithError("Pesan tidak ditemukan", 404);
        }

        // Security check
        if ($user->role === 'admin') {
            if ($chat['pengirim'] !== 'admin' || (int)$chat['id_admin'] !== (int)$user->uid) {
                return $this->respondWithError("Akses ditolak", 403);
            }
        } else {
            if ($chat['pengirim'] !== 'user' || (int)$chat['id_pembeli'] !== (int)$user->uid) {
                return $this->respondWithError("Akses ditolak", 403);
            }
        }

        $chatModel->update($id, [
            'is_deleted' => 1
        ]);

        return $this->respondWithSuccess("Pesan berhasil dihapus");
    }
}
