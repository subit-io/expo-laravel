<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceIdToExpoNotificationRecipients extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('exponent-push-notifications.recipients.database.table_name'), function (Blueprint $table) {
            $table->string('device_id')
                ->nullable();

            $table->dropUnique(['type', 'id', 'token']);
            $table->unique(['type', 'id', 'device_id'], 'expo_notification_recipients_type_id_token_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('exponent-push-notifications.recipients.database.table_name'), function (Blueprint $table) {
            $table->dropColumn('device_id');
            $table->dropUnique(['type', 'id', 'device_id']);
            $table->unique(['type', 'id', 'token'], 'expo_notification_recipients_type_id_token_unique');
        });
    }
}

;
