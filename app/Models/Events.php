<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Policies\EventsPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Laravel\Scout\Searchable;

#[UsePolicy(EventsPolicy::class)]
class Events extends Model
{
    use Searchable;
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];


    public function setNameAttribute($key)
    {
        $this->attributes['name'] = $key;
        $this->attributes['slug'] = Str::slug($key);
    }

    protected static function booted()
    {
        static::creating(function ($event) {
            if (Auth::check()) {
                $event->created_by = Auth::id();
            }
        });
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'status' => $this->status,
        ];
    }

    public function guests()
    {
        return $this->hasMany(Guests::class);
    }
}
