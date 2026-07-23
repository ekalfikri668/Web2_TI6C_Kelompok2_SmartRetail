<?php

namespace App\Controllers;

use App\Models\AlamatModel;

class AddressController extends BaseController
{
    public function index()
    {
        $user = $this->request->decodedToken;
        $alamatModel = new AlamatModel();
        $addresses = $alamatModel->where('id_pembeli', $user->uid)->findAll();
        return $this->respondWithSuccess("Daftar alamat berhasil diambil", $addresses);
    }

    public function create()
    {
        $user = $this->request->decodedToken;
        $rules = [
            'nama_penerima'  => 'required|min_length[3]|max_length[100]',
            'no_hp'          => 'required|min_length[10]|max_length[20]',
            'alamat_lengkap' => 'required',
            'kota'           => 'required|max_length[100]',
            'kode_pos'       => 'required|max_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->respondWithError("Validasi alamat gagal", 400, $this->validator->getErrors());
        }

        $alamatModel = new AlamatModel();
        $data = [
            'id_pembeli' => $user->uid,
            'nama_penerima' => $this->request->getVar('nama_penerima'),
            'no_hp' => $this->request->getVar('no_hp'),
            'alamat_lengkap' => $this->request->getVar('alamat_lengkap'),
            'kota' => $this->request->getVar('kota'),
            'kode_pos' => $this->request->getVar('kode_pos')
        ];

        $alamatId = $alamatModel->insert($data);
        return $this->respondWithSuccess("Alamat berhasil ditambahkan", ['id_alamat' => $alamatId], 201);
    }

    public function setUtama($id)
    {
        $user = $this->request->decodedToken;
        $alamatModel = new AlamatModel();

        // Verify ownership
        $addr = $alamatModel->where('id_alamat', $id)->where('id_pembeli', $user->uid)->first();
        if (!$addr) {
            return $this->respondWithError("Alamat tidak ditemukan", 404);
        }

        // Unset all existing primary addresses for this user
        $alamatModel->where('id_pembeli', $user->uid)->set(['is_utama' => 0])->update();

        // Set selected address as primary
        $alamatModel->update($id, ['is_utama' => 1]);

        return $this->respondWithSuccess("Alamat utama berhasil diubah", null);
    }

    public function delete($id)
    {
        $user = $this->request->decodedToken;
        $alamatModel = new AlamatModel();

        // Verify ownership
        $addr = $alamatModel->where('id_alamat', $id)->where('id_pembeli', $user->uid)->first();
        if (!$addr) {
            return $this->respondWithError("Alamat tidak ditemukan", 404);
        }

        $alamatModel->delete($id);
        return $this->respondWithSuccess("Alamat berhasil dihapus", null);
    }
}
