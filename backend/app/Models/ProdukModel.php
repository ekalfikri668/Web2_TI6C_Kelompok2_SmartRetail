<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'produk';
    protected $primaryKey       = 'id_produk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_brand', 'id_kategori', 'nama_produk', 'harga', 'stok', 'gambar', 'deskripsi', 'processor', 'ram', 'storage', 'gpu', 'layar', 'garansi', 'baterai', 'berat', 'os', 'konektivitas', 'kamera', 'resolusi'];

    // Dates
    protected $useTimestamps = false;
}
