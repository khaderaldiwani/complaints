<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('address');
            $table->text('description');
            $table->string('file')->nullable();
            $table->unsignedBigInteger('agency_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('status')->default(1);
            $table->string('noti')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();


            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};
