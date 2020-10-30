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
Route::get('slice/test','Web\TestController@slicetest');
//Route::get('/', function () {return view('welcome');}); //欢迎页面
Route::post('web/vranchor/register','Web\LoginController@register');
//Route::get('/', function () {return view('homepage');}); //首页
Route::get('test','Web\TestController@test');//测试
Route::post('sliceUpload','Web\TestController@sliceUpload');//上传
//Route::get('sliceDownload','Web\TestController@sliceDownload');//下载
Route::get('api/vranchor/sliceDownload','Api\VideoController@sliceDownload');//视频下载
//Route::get('list','Web\TestController@list');
Route::group(['prefix'=>'web','middleware'=>'check.login'],function (){
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
    Route::post('vranchor/cancelcollect','Web\IndexController@cancelcollect'); //取消收藏
    Route::get('vranchor/filetype','Web\IndexController@filetype'); //视频类型
    Route::post('vranchor/orderuserinfo','Web\UserController@orderuserinfo'); //用户信息
});

Route::group(['prefix'=>'admin','middleware'=>'check.login'],function (){
    Route::get('vranchor/index','Admin\IndexController@index');  //首页m,,,,,,,,,,,,kkkkkkkkkkkkkkkkkkk,mkkkkkkkkkkkkkkkjjjjjjjjjk
    Route::get('vranchor/userinfo','Admin\UserController@userinfo'); //用户信息
    Route::post('vranchor/updateinfo','Admin\UserController@updateinfo'); //修改用户信息
    Route::post('vranchor/updatepassword','Admin\UserController@updatepassword'); //修改密码
    Route::get('vranchor/userlist','Admin\UserController@userlist'); //用户列表
    Route::get('vranchor/slicelist','Admin\VideoController@slicelist');//视频列表
    Route::post('vranchor/updatestatus','Admin\VideoController@updatestatus');//修改状态
    Route::post('vranchor/deleteslice','Admin\VideoController@deleteslice');//删除视频
    Route::post('vranchor/batchupdatestatus','Admin\VideoController@batchupdatestatus');//批量修改状态
    Route::post('vranchor/batchdeleteslice','Admin\VideoController@batchdeleteslice');//批量删除视频
    Route::post('vranchor/updateuserinfo','Admin\UserController@updateuserinfo'); //修改用户信息

});


