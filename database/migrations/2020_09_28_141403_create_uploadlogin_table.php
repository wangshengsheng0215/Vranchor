<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUploadloginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploadlogin', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            //filename filepath username uid addtime  thumname thumpath status
            $table->string('filename')->comment('视频名称');
            $table->string('filepath')->comment('视频路径');
            $table->integer('uid')->comment('用户id');
            $table->string('username')->comment('用户名称');
            $table->string('thumname')->comment('缩略图名称');
            $table->string('thumpath')->comment('缩略图路径');
            $table->integer('status')->comment('状态');
            $table->string('remark')->comment('视频描述');
            $table->string('slicesize')->comment('视频大小');
            $table->integer('pvnum')->comment('视频点击量');
            $table->integer('uvnum')->comment('视频浏览量');
            $table->timestamp('addtime')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('添加时间');
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
        Schema::dropIfExists('uploadlogin');
    }
}
