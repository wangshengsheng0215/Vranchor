<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndexController extends Controller
{
    //移动端首页
    public function index(Request $request){
                $sql3 = 'SELECT
                        t1.id as sliceid,
                        t1.pvnum as palynum,
                        t1.id,
                        t1.file_title,
                        t1.file_type,
                        t1.remark,
                        t1.filepath,
                        t1.thumpath,
                        t1.uid,
                        t1.username,
						t3.head_portrait,
                        t1.status,
                        t1.addtime
                        FROM uploadlogin t1 INNER JOIN users t3 ON t1.uid = t3.id WHERE EXISTS
                        (SELECT COUNT(*)  FROM uploadlogin t2
                        WHERE  t2.file_type = t1.file_type AND t2.addtime > t1.addtime HAVING t1.status = 1 AND COUNT(*) < 2 )
                        ORDER BY t1.file_type';
                $data3 = DB::select($sql3);
                $typelist = DB::select('SELECT id,name FROM slicetype');
                $data = [];
                $filetype = [];
                foreach ($typelist as $k => $v) {
                    $filetype[$v->id] = $v->name;
                }
                $data['filetype'] = $filetype;
                $data['typeslice'] = $data3;
                return json_encode(['errcode' => '1', 'errmsg' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
    }

    //移动端更多视频
    public function moreslice(Request $request){
                //规则
                try {
                    $rules = [
                        'file_type'=>'required',
                    ];
                    //自定义消息
                    $messages = [
                        'file_type.required' => '视频分类值不能为空',
                    ];

                    $this->validate($request, $rules, $messages);

                    $file_type = $request->file_type;
                    $paginate = $request->input('paginate');
                    if(empty($paginate)){
                        $paginate = 10;
                    }
                    $nowtime = date('Y-m-d');
                    $lasttime = date("Y-m-d",strtotime("-1 day"));
                    $sql1 = 'SELECT
                        t1.sliceid,
                        t1.playnum,
                        t2.id,
                        t2.file_title,
                        t2.file_type,
                        t2.remark,
                        t2.filepath,
                        t2.thumpath,
                        t2.uid,
                        t2.username,
						t3.head_portrait,
                        t2.status,
                        t1.addtime
                        FROM ( uploadlogin t2 INNER JOIN users t3 ON t2.uid = t3.id)
                        INNER JOIN playcollect t1 ON t1.sliceid = t2.id
                         WHERE t2.status = 1 AND t2.file_type = ? AND t1.addtime > ? AND t1.addtime < ?  ORDER BY t1.playnum DESC LIMIT 0,2';
                    $data1 = DB::select($sql1,[$file_type,$lasttime,$nowtime]);
                    $sql2 = 'SELECT
                        t2.id as sliceid,
                        t2.pvnum as playnum,
                        t2.id,
                        t2.file_title,
                        t2.file_type,
                        t2.remark,
                        t2.filepath,
                        t2.thumpath,
                        t2.uid,
                        t2.username,
						t3.head_portrait,
                        t2.status,
                        t2.addtime
                         FROM uploadlogin t2 INNER JOIN users t3 ON t3.id = t2.uid WHERE t2.status = 1 AND t2.file_type = %s ORDER BY addtime DESC';

                    $sql2Tmp = sprintf($sql2,$file_type);
                    $data2 = DB::table(DB::raw("($sql2Tmp) as t"))->paginate($paginate);
                    $data = [];
                    $data['lastpaly'] = $data1;
                    $data['newslice'] = $data2;

                    return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }

    }

    //移动端相关推荐
    //相关推荐
    public function slicerecommend(Request $request){

            //规则
            try {
                $rules = [
                    'file_type'=>'required',
                ];
                //自定义消息
                $messages = [
                    'file_type.required' => '视频分类值不能为空',
                ];

                $this->validate($request, $rules, $messages);

                $file_type = $request->file_type;
                $sql2 = 'SELECT
                        t2.id as sliceid,
                        t2.pvnum as playnum,
                        t2.id,
                        t2.file_title,
                        t2.file_type,
                        t2.remark,
                        t2.filepath,
                        t2.thumpath,
                        t2.uid,
                        t2.username,
						t3.head_portrait,
                        t2.status,
                        t2.addtime
                         FROM uploadlogin t2 INNER JOIN users t3 ON t3.id = t2.uid WHERE t2.status = 1 AND t2.file_type = ? ORDER BY addtime DESC limit 0,6';
                $data2 = DB::select($sql2,[$file_type]);
                $data = [];
                $data['slicerecommend'] = $data2;

                return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

    }

}
