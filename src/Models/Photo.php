<?php

namespace App\Models;

class Photo extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'photo';
    protected $primaryKey = 'id_photo';
    public $timestamps = false;

    public function annonce()
    {
        return $this->belongsTo('App\Models\Annonce', 'id_annonce');
    }
}
