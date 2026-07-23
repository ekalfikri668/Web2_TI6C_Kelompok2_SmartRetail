<?php

namespace App\Models;

use CodeIgniter\Model;

class PembeliModel extends Model
{
    protected $table            = 'pembeli';
    protected $primaryKey       = 'id_pembeli';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_pembeli', 'email', 'password', 'no_hp', 'foto_profil', 'tanggal_daftar', 'status', 'halaman_utama'];

    // Dates
    protected $useTimestamps = false;
}
