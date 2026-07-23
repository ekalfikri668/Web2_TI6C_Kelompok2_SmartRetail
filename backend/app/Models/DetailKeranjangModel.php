<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailKeranjangModel extends Model
{
    protected $table            = 'detail_keranjang';
    protected $primaryKey       = 'id_detail';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_keranjang', 'id_produk', 'jumlah', 'subtotal', 'warna', 'tipe'];

    // Dates
    protected $useTimestamps = false;
}
