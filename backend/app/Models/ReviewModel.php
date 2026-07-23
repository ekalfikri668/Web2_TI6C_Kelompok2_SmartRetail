<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'review';
    protected $primaryKey       = 'id_review';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pembeli', 'id_produk', 'rating', 'komentar', 'foto_review', 'tanggal', 'balasan_admin'];

    // Dates
    protected $useTimestamps = false;
}
