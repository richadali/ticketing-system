<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
            'name',
            'status',
            'description',
            'assigned_to',
            'created_by',
            'closed_at',
            'deadline',
            'category',
            'urgent',
            'sub_company',
        ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'closed_at' => 'datetime',
        'deadline' => 'date',
    ];

    /**
     * Get the user to whom the ticket is assigned.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the ticket.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attachments for the ticket.
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the activities for the ticket.
     */
    public function activities()
        {
            return $this->hasMany(TicketActivity::class)->orderBy('created_at', 'desc');
        }
    
        /**
         * Get the comments for the ticket.
         */
        public function comments()
        {
            return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
        }
    }
