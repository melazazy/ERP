<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'code', 'subcategory_id'];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    public function trusts()
    {
        return $this->hasMany(Trust::class);
    }

    public function receivings()
    {
        return $this->hasMany(Receiving::class);
    }
}
