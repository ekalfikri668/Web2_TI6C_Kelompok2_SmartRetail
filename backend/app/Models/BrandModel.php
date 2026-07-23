<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table            = 'brand';
    protected $primaryKey       = 'id_brand';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_brand', 'logo', 'deskripsi'];

    // Dates
    protected $useTimestamps = false;
}
