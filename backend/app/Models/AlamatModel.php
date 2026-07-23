<?php

namespace App\Models;

use CodeIgniter\Model;

class AlamatModel extends Model
{
    protected $table            = 'alamat';
    protected $primaryKey       = 'id_alamat';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pembeli', 'nama_penerima', 'no_hp', 'alamat_lengkap', 'kota', 'kode_pos', 'is_utama'];

    // Dates
    protected $useTimestamps = false;
}
