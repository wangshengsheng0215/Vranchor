<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id')->comment('主键id');
            $table->string('username')->unique()->comment('用户名');
            $table->string('mobile',13)->unique()->comment('手机号');
            $table->string('password')->comment('密码');
            $table->tinyInteger('role')->comment('身份 1为admin 2为普通用户');
            $table->tinyInteger('status')->comment('状态');
            $table->string('name')->nullable()->comment('姓名昵称');
            $table->string('logo')->nullable()->comment('logo');
            $table->string('head_portrait')->nullable()->comment('头像');
            $table->timestamp('addtime')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('添加时间');
            $table->timestamp('updatetime')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('修改时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
