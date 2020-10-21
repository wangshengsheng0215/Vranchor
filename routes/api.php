<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('vranchor/register','Api\LoginController@register');
Route::post('vranchor/login','Api\LoginController@login');

Route::post('vranchor/sendcode','Api\UserController@sendcode');
Route::post('vranchor/findPassword','Api\UserController@findPassword');

Route::group(['prefix'=>'vranchor','middleware'=>'check.login'],function (){
    Route::get('userlist','Api\UserController@userlist');
    Route::post('sliceUpload','APi\VideoController@sliceUpload');//上传视频
    Route::post('sliceplay','Api\VideoController@sliceplay');//视频播放
    Route::post('slicecollect','Api\VideoController@slicecollect');//视频收藏
    Route::get('sliceDownload','Api\VideoController@sliceDownload');//视频下载
    Route::post('sliceinfo','Api\VideoController@sliceinfo');//视频信息
    Route::post('slicerecommend','Api\VideoController@slicerecommend');//视频推荐
    Route::post('cancelcollect','Api\VideoController@cancelcollect'); //取消收藏
});
