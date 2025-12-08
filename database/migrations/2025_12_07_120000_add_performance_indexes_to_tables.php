<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Indexes for complaints table
        Schema::table('complaints', function (Blueprint $table) {
            // Single column indexes
            $table->index('status', 'idx_complaints_status');
            $table->index('user_id', 'idx_complaints_user_id');
            $table->index('agency_id', 'idx_complaints_agency_id');
            $table->index('created_at', 'idx_complaints_created_at');
            $table->index('locked_by', 'idx_complaints_locked_by');
            
            // Composite indexes for common queries
            $table->index(['status', 'agency_id'], 'idx_complaints_status_agency');
            $table->index(['user_id', 'status'], 'idx_complaints_user_status');
            $table->index(['agency_id', 'status', 'created_at'], 'idx_complaints_agency_status_created');
        });

        // Indexes for notifications table
        Schema::table('notifications', function (Blueprint $table) {
            // Single column indexes
            $table->index('user_id', 'idx_notifications_user_id');
            $table->index('is_read', 'idx_notifications_is_read');
            $table->index('created_at', 'idx_notifications_created_at');
            
            // Composite index for common query: get unread notifications for user
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_notifications_user_read_created');
        });

        // Indexes for complaint_history table
        Schema::table('complaint_history', function (Blueprint $table) {
            // Single column indexes
            $table->index('complaint_id', 'idx_complaint_history_complaint_id');
            $table->index('administrative_id', 'idx_complaint_history_admin_id');
            $table->index('date', 'idx_complaint_history_date');
            $table->index('action', 'idx_complaint_history_action');
            
            // Composite index for common query: get history for complaint ordered by date
            $table->index(['complaint_id', 'date'], 'idx_complaint_history_complaint_date');
        });

        // Indexes for jobs table (Queue)
        Schema::table('jobs', function (Blueprint $table) {
            // Already has index on queue, but add index on available_at for better performance
            $table->index('available_at', 'idx_jobs_available_at');
            $table->index(['queue', 'available_at'], 'idx_jobs_queue_available');
        });

        // Indexes for administrative table (if needed)
        Schema::table('administrative', function (Blueprint $table) {
            $table->index('role', 'idx_administrative_role');
            $table->index('id_agency', 'idx_administrative_agency_id');
            $table->index(['role', 'id_agency'], 'idx_administrative_role_agency');
        });

        // Indexes for users table (if needed)
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
            $table->index('is_verified', 'idx_users_is_verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('idx_complaints_status');
            $table->dropIndex('idx_complaints_user_id');
            $table->dropIndex('idx_complaints_agency_id');
            $table->dropIndex('idx_complaints_created_at');
            $table->dropIndex('idx_complaints_locked_by');
            $table->dropIndex('idx_complaints_status_agency');
            $table->dropIndex('idx_complaints_user_status');
            $table->dropIndex('idx_complaints_agency_status_created');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_is_read');
            $table->dropIndex('idx_notifications_created_at');
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_user_read_created');
        });

        Schema::table('complaint_history', function (Blueprint $table) {
            $table->dropIndex('idx_complaint_history_complaint_id');
            $table->dropIndex('idx_complaint_history_admin_id');
            $table->dropIndex('idx_complaint_history_date');
            $table->dropIndex('idx_complaint_history_action');
            $table->dropIndex('idx_complaint_history_complaint_date');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('idx_jobs_available_at');
            $table->dropIndex('idx_jobs_queue_available');
        });

        Schema::table('administrative', function (Blueprint $table) {
            $table->dropIndex('idx_administrative_role');
            $table->dropIndex('idx_administrative_agency_id');
            $table->dropIndex('idx_administrative_role_agency');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_is_verified');
        });
    }
};

