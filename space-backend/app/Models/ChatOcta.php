<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatOcta extends Model
{
    protected $table = 'chat_octa';

    protected $fillable = [
        'octa_id',
        'number',
        'channel',
        'contact_id',
        'contact_name',
        'agent_id',
        'agent_name',
        'agent_email',
        'lastMessageDate',
        'status',
        'closedAt',
        'group_id',
        'group_name',
        'tags',
        'withBot',
        'unreadMessages'
    ];

    protected $casts = [
        'tags' => 'array',
        'withBot' => 'boolean',
        'unreadMessages' => 'integer'
    ];
}
