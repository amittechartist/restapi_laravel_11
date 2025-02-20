<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'user_id', 'message', 'file'
    ];

    // The ticket this reply belongs to
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // The user (or admin) who replied
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
