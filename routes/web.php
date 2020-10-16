<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {return view('welcome');}); //欢迎页面
Route::post('web/vranchor/register','Web\LoginController@register');
//Route::get('/', function () {return view('homepage');}); //首页
//Route::get('test','Web\TestController@test');//测试
//Route::post('sliceUpload','Web\TestController@sliceUpload');//上传
//Route::get('sliceDownload','Web\TestController@sliceDownload');//下载
//Route::get('list','Web\TestController@list');
Route::group(['prefix'=>'web','middleware'=>'check.login'],function (){
    Route::get('vranchor/slicelist','Web\VideoController@slicelist');
    Route::get('vranchor/index','Web\IndexController@index');  //首页
    Route::get('vranchor/userlist','Web\UserController@userlist'); //用户列表
    Route::get('vranchor/userinfo','Web\UserController@userinfo'); //用户信息
    Route::post('vranchor/updateinfo','Web\UserController@updateinfo'); //修改用户信息
    Route::post('vranchor/updatepassword','Web\UserController@updatepassword'); //修改密码
    Route::post('vranchor/moreslice','Web\IndexController@moreslice'); //更多视频
    Route::post('vranchor/myslice','Web\IndexController@myslice'); //我的视频
    Route::post('vranchor/mycollect','Web\IndexController@mycollect'); //我的收藏
    Route::post('vranchor/myhistory','Web\IndexController@myhistory'); //历史记录
    Route::post('vranchor/search','Web\IndexController@search');//搜索
});
