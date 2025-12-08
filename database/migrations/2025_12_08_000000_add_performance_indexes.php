<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('agency_id');
            $table->index('created_at');
            $table->index(['status', 'agency_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('is_read');
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });

        Schema::table('complaint_history', function (Blueprint $table) {
            $table->index('complaint_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['agency_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'agency_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_read']);
            $table->dropIndex(['user_id', 'is_read']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('complaint_history', function (Blueprint $table) {
            $table->dropIndex(['complaint_id']);
            $table->dropIndex(['date']);
        });
    }
};

