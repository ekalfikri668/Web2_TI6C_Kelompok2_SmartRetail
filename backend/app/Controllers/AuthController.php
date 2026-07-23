<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\PembeliModel;

class AuthController extends BaseController
{
    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi gagal", 400, $this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // 1. Check in admin table
        $adminModel = new AdminModel();
        $admin = $adminModel->where('email', $email)->first();

        if ($admin) {
            // Support both plain text (for easy testing/sql seeds) and hashed passwords
            $pwMatch = ($password === $admin['password']) || password_verify($password, $admin['password']);
            if ($pwMatch) {
                $tokenData = [
                    'id' => $admin['id_admin'],
                    'email' => $admin['email'],
                    'role' => 'admin',
                    'nama' => $admin['nama_admin']
                ];
                $token = generateJWT($tokenData);
                return $this->respondWithSuccess("Login berhasil sebagai Admin", [
                    'token' => $token,
                    'user' => [
                        'id_admin' => $admin['id_admin'],
                        'nama_admin' => $admin['nama_admin'],
                        'email' => $admin['email'],
                        'role' => 'admin',
                        'foto' => $admin['foto']
                    ]
                ]);
            }
        }

        // 2. Check in pembeli table
        $pembeliModel = new PembeliModel();
        $pembeli = $pembeliModel->where('email', $email)->first();

        if ($pembeli) {
            $pwMatch = ($password === $pembeli['password']) || password_verify($password, $pembeli['password']);
            if ($pwMatch) {
                if ($pembeli['status'] === 'nonaktif') {
                    return $this->respondWithError("Akun Anda dinonaktifkan. Hubungi admin.", 403);
                }

                $tokenData = [
                    'id' => $pembeli['id_pembeli'],
                    'email' => $pembeli['email'],
                    'role' => 'pembeli',
                    'nama' => $pembeli['nama_pembeli']
                ];
                $token = generateJWT($tokenData);
                return $this->respondWithSuccess("Login berhasil", [
                    'token' => $token,
                    'user' => [
                        'id_pembeli' => $pembeli['id_pembeli'],
                        'nama_pembeli' => $pembeli['nama_pembeli'],
                        'email' => $pembeli['email'],
                        'role' => 'pembeli',
                        'no_hp' => $pembeli['no_hp'],
                        'foto_profil' => $pembeli['foto_profil']
                    ]
                ]);
            }
        }

        return $this->respondWithError("Email atau password salah", 400);
    }

    public function register()
    {
        $rules = [
            'nama_pembeli' => 'required|min_length[3]|max_length[100]',
            'email'        => 'required|valid_email',
            'password'     => 'required|min_length[6]',
            'no_hp'        => 'required|min_length[10]|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi registrasi gagal", 400, $this->validator->getErrors());
        }

        $pembeliModel = new PembeliModel();
        $email = $this->request->getVar('email');

        // Check duplicate
        $existingPembeli = $pembeliModel->where('email', $email)->first();
        $adminModel = new AdminModel();
        $existingAdmin = $adminModel->where('email', $email)->first();

        if ($existingPembeli || $existingAdmin) {
            return $this->respondWithError("Email sudah terdaftar", 400, ['email' => 'Email sudah terdaftar dalam sistem.']);
        }

        $data = [
            'nama_pembeli' => $this->request->getVar('nama_pembeli'),
            'email' => $email,
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'no_hp' => $this->request->getVar('no_hp'),
            'foto_profil' => null,
            'tanggal_daftar' => date('Y-m-d'),
            'status' => 'aktif'
        ];

        if ($pembeliModel->insert($data)) {
            return $this->respondWithSuccess("Registrasi berhasil", null, 201);
        }

        return $this->respondWithError("Gagal mendaftarkan akun. Coba beberapa saat lagi.", 500);
    }

    public function updateProfile()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa memperbarui profil", 403);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $namaPembeli = trim($input['nama_pembeli'] ?? $this->request->getVar('nama_pembeli') ?? '');
        $email       = trim($input['email']        ?? $this->request->getVar('email')        ?? '');
        $noHp        = trim($input['no_hp']        ?? $this->request->getVar('no_hp')        ?? '');

        if (empty($namaPembeli) || empty($email)) {
            return $this->respondWithError("Nama dan email wajib diisi", 400);
        }

        $pembeliModel = new PembeliModel();

        // Check email uniqueness (excluding self)
        $existing = $pembeliModel->where('email', $email)->where('id_pembeli !=', $user->uid)->first();
        if ($existing) {
            return $this->respondWithError("Email sudah digunakan oleh akun lain", 400);
        }

        $pembeliModel->update($user->uid, [
            'nama_pembeli' => $namaPembeli,
            'email'        => $email,
            'no_hp'        => $noHp,
        ]);

        return $this->respondWithSuccess("Profil berhasil diperbarui", null);
    }

    public function updatePassword()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa mengubah password", 403);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $passwordLama = $input['password_lama'] ?? $this->request->getVar('password_lama') ?? '';
        $passwordBaru = $input['password_baru'] ?? $this->request->getVar('password_baru') ?? '';

        if (empty($passwordLama) || empty($passwordBaru)) {
            return $this->respondWithError("Password lama dan password baru wajib diisi", 400);
        }

        if (strlen($passwordBaru) < 6) {
            return $this->respondWithError("Password baru minimal 6 karakter", 400);
        }

        $pembeliModel = new PembeliModel();
        $pembeli = $pembeliModel->find($user->uid);

        if (!$pembeli) {
            return $this->respondWithError("Akun tidak ditemukan", 404);
        }

        $pwMatch = ($passwordLama === $pembeli['password']) || password_verify($passwordLama, $pembeli['password']);
        if (!$pwMatch) {
            return $this->respondWithError("Password lama tidak sesuai", 400);
        }

        $pembeliModel->update($user->uid, [
            'password' => password_hash($passwordBaru, PASSWORD_BCRYPT)
        ]);

        return $this->respondWithSuccess("Password berhasil diubah", null);
    }

    public function updateHomepage()
    {
        $user = $this->request->decodedToken;
        if ($user->role !== 'pembeli') {
            return $this->respondWithError("Hanya pembeli yang bisa mengatur halaman utama", 403);
        }

        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = null;
        }
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $allowed  = ['home.php', 'produk.php', 'pesanan.php', 'profil.php'];
        $halaman  = $input['halaman_utama'] ?? $this->request->getVar('halaman_utama') ?? 'home.php';

        if (!in_array($halaman, $allowed)) {
            return $this->respondWithError("Halaman tidak valid", 400);
        }

        $pembeliModel = new PembeliModel();
        $pembeliModel->update($user->uid, ['halaman_utama' => $halaman]);

        return $this->respondWithSuccess("Halaman utama berhasil diatur", null);
    }
}
