<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table            = 'chat';
    protected $primaryKey       = 'id_chat';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pembeli', 'id_admin', 'pesan', 'gambar', 'pengirim', 'waktu', 'is_read', 'is_edited', 'is_deleted'];

    // Dates
    protected $useTimestamps = false;
}
