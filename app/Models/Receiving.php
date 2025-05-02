<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Receiving extends Model
{
    use HasFactory;
    
protected $casts = [
    'received_at' => 'datetime',
];
    protected $fillable = ['item_id', 'supplier_id', 'department_id', 'quantity', 'unit_price', 'unit_id', 'received_at', 'receiving_number', 'tax', 'discount'];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function receivingNumber()
    {
        return $this->belongsTo(ReceivingNumber::class);
    }
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_receiving')
                ->withPivot('quantity', 'unit_price')
                ->withTimestamps();
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
