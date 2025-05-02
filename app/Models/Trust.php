<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trust extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'department_id', 'quantity', 'requested_by', 'requisition_number', 'status'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}