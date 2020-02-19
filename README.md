# Exponent push notifications channel for Laravel
[![Latest Version on Packagist](https://img.shields.io/packagist/v/subit/expo-laravel.svg?style=flat-square)](https://packagist.org/packages/subit/expo-laravel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/subit/expo-laravel.svg?style=flat-square)](https://packagist.org/packages/subit/expo-laravel)

## Contents

-   [Installation](#installation)
-   [Usage](#usage) - [ExpoMessage options](#expomessage-options)

## Installation

You can install the package via composer:

```bash
composer require subit-io/laravel-expo-notifications
```

If you are using Laravel 5.5 or higher this package will automatically register itself using [Package Discovery](https://laravel.com/docs/5.5/packages#package-discovery). For older versions of Laravel you must install the service provider manually:

```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\ExpoPushNotifications\ExpoPushNotificationsServiceProvider::class,
],

```

Before publish exponent notification migration you must add in .env file:

```bash
EXPONENT_PUSH_NOTIFICATION_RECIPIENTS_STORAGE_DRIVER=database
```

You can publish the migration with:

```bash
php artisan vendor:publish --provider="NotificationChannels\ExpoPushNotifications\ExpoPushNotificationsServiceProvider" --tag="migrations"
```

After publishing the migration you can create the `expo_notification_recipients` table by running the migrations:

```bash
php artisan migrate
```

You can optionally publish the config file with:

```bash
php artisan vendor:publish --provider="NotificationChannels\ExpoPushNotifications\ExpoPushNotificationsServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'recipients' => [
        /*
         * Supported: "database"
         */
        'driver' => env('EXPONENT_PUSH_NOTIFICATION_RECIPIENTS_STORAGE_DRIVER', 'file'),

        'database' => [
            'table_name' => 'expo_notification_recipients',
        ],
    ]
];
```

## Usage

```php
use NotificationChannels\ExpoPushNotifications\ExpoChannel;
use NotificationChannels\ExpoPushNotifications\ExpoMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    public function toExpoPush($notifiable)
    {
        return ExpoMessage::create()
            ->badge(1)
            ->enableSound()
            ->title("Congratulations!")
            ->body("Your {$notifiable->service} account was approved!");
    }
}
```
## `ExpoMessage` options

|     Property     | iOS/Android |   Type   |                           Description                          |
|:----------------:|:-----------:|:--------:|:--------------------------------------------------------------:|
|      `to()`      |     both    |  string  | An Expo push token specifying the recipient of this message    |
|   `jsonData()`   |     both    |  string  | A JSON object delivered to your app. It may be up to 4KiB      |
|     `title()`    |     both    |  string  | The title to display in the notification                       |
|     `body()`     |     both    |  string  | The message to display in the notification                     |
|      `ttl()`     |     both    |    int   | Seconds the message may be kept around for redelivery          |
|  `expiration()`  |     both    |    int   | UNIX epoch timestamp. Same effect as ttl. ttl takes precedence |
|   `priority()`   |     both    | Priority | The delivery priority of the message                           |
|   `subtitle()`   |     iOS     |  string  | The subtitle to display in the notification below the title    |
|  `enableSound()` |     iOS     |          | Play a sound when the recipient receives this notification     |
| `disableSound()` |     iOS     |          | Play no sound (default)                                        |
|     `badge()`    |     iOS     |    int   | Number to display in the badge on the app icon                 |
|   `channelId()`  |   Android   |  string  | Channel through which to display this notification             |

For a more detailed description, refer to the Expo documentation https://docs.expo.io/versions/latest/guides/push-notifications/#formats

### Managing Recipients

This package registers two endpoints that handle the subscription of recipients, the endpoints are defined in src/Http/routes.php file, used by ExpoController and all loaded through the package service provider. 

### Routing a message

By default the expo "recipient" messages will be sent to will be defined (besides the id) using the notifiable class as type, for example `App\User`. However, you can change this behaviour by including a `routeNotificationForExpoPushNotifications()` in the notifiable class method that returns the recipient type.
