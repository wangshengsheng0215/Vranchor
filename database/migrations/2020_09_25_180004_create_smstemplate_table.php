<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmstemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smstemplate', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->integer('templatetype')->comment('场景类型0：验证码。1：短信通知。2：推广短信。3：国际/港澳台消息');
            $table->string('templatecode')->comment('短信模板code');
            $table->string('templatename')->comment('模板名称');
            $table->text('templatecontent')->comment('模板内容');
            $table->string('remark')->comment('模板说明');
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
        Schema::dropIfExists('smstemplate');
    }
}
