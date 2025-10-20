<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Guests extends Model
{
    use Searchable;
    protected $guarded = [];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization' => $this->organization,
            'status' => $this->status,
        ];
    }


    public function event()
    {
        return $this->belongsTo(Events::class);
    }
}
