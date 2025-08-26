<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name',32)->unique();
            $table->char('mobile', 11)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('password');
            $table->string('nickname', 32)->nullable()->comment('用户昵称');
            $table->string('introduction', 64)->nullable()->comment('个性签名');
            $table->string('avatar')->nullable();
            $table->string('realname', 32)->nullable()->comment('真实姓名');
            $table->string('idcard', 18)->nullable()->index()->comment('身份证号');
            $table->timestamp('identity_verified_at')->nullable()->comment('实名认证时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
