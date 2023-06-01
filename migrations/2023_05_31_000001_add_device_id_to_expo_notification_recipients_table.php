<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('exponent-push-notifications.recipients.database.table_name'), function (Blueprint $table) {
            $table->string('device_id')
                ->unique()
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('exponent-push-notifications.recipients.database.table_name'), function (Blueprint $table) {
            $table->dropColumn('device_id');
        });

    }
};
