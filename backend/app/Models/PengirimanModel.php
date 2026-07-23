<?php

namespace App\Models;

use CodeIgniter\Model;

class PengirimanModel extends Model
{
    protected $table            = 'pengiriman';
    protected $primaryKey       = 'id_pengiriman';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pesanan', 'ekspedisi', 'nomor_resi', 'status_pengiriman', 'estimasi_tiba'];

    // Dates
    protected $useTimestamps = false;
}
