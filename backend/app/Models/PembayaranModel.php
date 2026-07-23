<?php

namespace App\Models;

use CodeIgniter\Model;

class PembayaranModel extends Model
{
    protected $table            = 'pembayaran';
    protected $primaryKey       = 'id_pembayaran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pesanan', 'metode', 'jumlah_bayar', 'bukti_bayar', 'tanggal_bayar', 'status'];

    // Dates
    protected $useTimestamps = false;
}
