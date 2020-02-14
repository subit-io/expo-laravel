<?php

/**
 * Here you may define the configuration for the expo-notifications-driver.
 * The expo-notifications-driver can guide the sdk to use `database` or `file` repositories.
 * The database repository uses the same configuration for the database in your Laravel app.
 */

return [
    'recipients' => [
        'database' => [
            'table_name' => 'expo_notification_recipients',
        ],
    ],
];
