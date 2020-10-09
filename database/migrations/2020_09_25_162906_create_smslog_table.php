<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmslogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smslog', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id')->comment('主键');
            $table->string('mobile',13)->comment('手机号');
            $table->string('session_id')->comment('session_id');
            $table->string('code',10)->comment('验证码');
            $table->integer('status')->comment('状态');
            $table->string('scene')->comment('模板');
            $table->timestamp('addtime')->comment('添加时间');
            $table->string('error_msg')->comment('上传状态');
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
        Schema::dropIfExists('smslog');
    }
}
