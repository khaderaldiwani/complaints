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
    Schema::create('complaint_history', function (Blueprint $table) {
        $table->id();
        $table->string('action');  
        $table->text('old_value')->nullable();
        $table->text('new_value')->nullable();
        $table->unsignedBigInteger('administrative_id'); 
        $table->unsignedBigInteger('complaint_id');      
        $table->timestamp('date');                      
        $table->timestamps();

        $table->foreign('administrative_id')->references('id')->on('administrative')->onDelete('cascade');
        $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('complaint_history');
}

};
