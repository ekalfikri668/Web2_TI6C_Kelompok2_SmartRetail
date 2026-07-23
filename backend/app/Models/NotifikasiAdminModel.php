<?php

namespace App\Models;

use CodeIgniter\Model;

class NotifikasiAdminModel extends Model
{
    protected $table            = 'notifikasi_admin';
    protected $primaryKey       = 'id_notifikasi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['judul', 'isi', 'tipe', 'id_referensi', 'status_baca', 'tanggal'];

    // Dates
    protected $useTimestamps = false;
}
