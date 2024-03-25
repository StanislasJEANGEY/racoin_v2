<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annonce extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'annonce';
    protected $primaryKey = 'id_annonce';
    public $timestamps = false;
    public ?string $links = null;


    public function annonceur(): BelongsTo
    {
        return $this->belongsTo('App\Models\Annonceur', 'id_annonceur');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo('App\Models\Departement', 'id_departement');
    }

    public function photo(): hasMany
    {
        return $this->hasMany('model\Photo', 'id_photo');
    }
}
