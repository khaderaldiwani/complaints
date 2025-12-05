<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up()
    {
        Schema::create('administrative', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('user_name')->unique();  
            $table->string('password');
            $table->unsignedTinyInteger('role'); // 1 = admin , 2 = employee
            $table->unsignedBigInteger('id_agency')->nullable(); 
             $table->boolean('status')->default(1); // 1 = active , 0 = disabled
        $table->unsignedTinyInteger('failed_attempts')->default(0);
        $table->timestamp('locked_until')->nullable();
            $table->timestamps();
            $table->foreign('id_agency')->references('id')->on('agencies')->onDelete('set null');
        });

        
        DB::table('administrative')->insert([
            'name' => 'Khader Aldiwani',
            'user_name' => 'Khader',
            'password' => Hash::make('12345678'), 
            'role' => 1, 
            'id_agency' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('administrative');
    }
};
