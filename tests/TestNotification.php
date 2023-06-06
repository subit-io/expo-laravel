<?php

namespace NotificationChannels\ExpoPushNotifications\Test;

use Illuminate\Notifications\Notification;
use Subit\ExpoSdk\ExpoMessage;

class TestNotification extends Notification
{
    public function toExpoPush($notifiable): ExpoMessage
    {
        return ExpoMessage::create()->title('Title');
    }
}

