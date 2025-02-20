<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subject', 'message', 'file', 'status'
    ];

    // The ticket creator
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Replies to this ticket
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
}
