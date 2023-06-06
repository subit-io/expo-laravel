<?php

namespace NotificationChannels\ExpoPushNotifications\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The associated table.
     *
     * @var string
     */
    protected $table;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'id',
        'token',
        'device_id'
    ];

    /**
     * Recipient constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('exponent-push-notifications.recipients.database.table_name');

        parent::__construct($attributes);
    }
}
