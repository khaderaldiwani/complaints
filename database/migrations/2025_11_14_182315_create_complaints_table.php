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
            $table->string('type');                      // نوع الشكوى
            $table->string('address');                   // العنوان
            $table->text('description');                 // التفاصيل
            $table->string('file')->nullable();          // مستند أو صورة
            $table->unsignedBigInteger('agency_id');     // الجهة
            $table->unsignedBigInteger('user_id');       // صاحب الشكوى
            $table->unsignedTinyInteger('status')->default(1); // 1,2,3,4
            $table->string('noti')->nullable();          // إشعار أو ملاحظة
             $table->unsignedBigInteger('locked_by')->nullable();
        $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            // العلاقات
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};
