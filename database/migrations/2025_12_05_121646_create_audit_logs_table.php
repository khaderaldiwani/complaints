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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('admin_id')->nullable();
        $table->string('action');                 // مثل: change_status, add_note
        $table->string('model');                  // users, complaints, administrative
        $table->unsignedBigInteger('model_id');   // رقم السجل المتأثر
        $table->json('old_value')->nullable();    
        $table->json('new_value')->nullable();
        $table->string('ip')->nullable();
        $table->timestamps();

        $table->foreign('admin_id')->references('id')->on('administrative')->onDelete('set null');
    });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
