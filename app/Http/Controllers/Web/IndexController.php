<?php

namespace App\Http\Controllers\Web;

use App\Models\Collect;
use App\Models\Playcollect;
use App\Models\Uploadlogin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndexController extends Controller
{
    //首页
    public function index(Request $request){

        $user = \Auth::user();
       // dd($user);
        if($user) {
            $role = $user->role;
            if ($role == 1) {
                //管理员权限
            } elseif ($role == 2) {

                //普通用户权限
                //昨日播放量最多的视频前10条
                $nowtime = date('Y-m-d');
                $lasttime = date("Y-m-d", strtotime("-1 day"));
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
                        t2.status,
                        t1.addtime
                        FROM playcollect t1
                        INNER JOIN uploadlogin t2 ON t1.sliceid = t2.id
                         WHERE status = 1 AND t1.addtime > ? AND t1.addtime < ? ORDER BY t1.playnum DESC LIMIT 0,5';
                //$sqlTmp = sprintf($sql,$lasttime,$nowtime);
                $data1 = DB::select($sql1, [$lasttime, $nowtime]);
//                $sql2 = 'SELECT
//                        id as sliceid,
//                        pvnum as playnum,
//                        id,
//                        file_title,
//                        file_type,
//                        remark,
//                        filepath,
//                        thumpath,
//                        uid,
//                        username,
//                        status,
//                        addtime
//                         FROM uploadlogin WHERE status = 1 ORDER BY addtime DESC LIMIT 0,6';
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
                         FROM uploadlogin t2 INNER JOIN users t3 ON t2.uid = t3.id WHERE t2.status = 1 ORDER BY t2.addtime DESC LIMIT 0,6';
                $data2 = DB::select($sql2);
//                $sql3 = 'SELECT
//                        t1.id as sliceid,
//                        t1.pvnum as palynum,
//                        t1.id,
//                        t1.file_title,
//                        t1.file_type,
//                        t1.remark,
//                        t1.filepath,
//                        t1.thumpath,
//                        t1.uid,
//                        t1.username,
//                        t1.status,
//                        t1.addtime
//                        FROM uploadlogin t1 WHERE EXISTS
//                        (SELECT COUNT(*)  FROM uploadlogin
//                        WHERE  file_type = t1.file_type AND addtime > t1.addtime HAVING status = 1 AND COUNT(*) < IF(file_type=2,5,4) )
//                        ORDER BY t1.file_type ';
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
                        WHERE  t2.file_type = t1.file_type AND t2.addtime > t1.addtime HAVING t1.status = 1 AND COUNT(*) < IF(file_type=2,5,4) )
                        ORDER BY t1.file_type';
                $data3 = DB::select($sql3);
                $typelist = DB::select('SELECT id,name FROM slicetype');
                $data = [];
                $filetype = [];
                foreach ($typelist as $k => $v) {
                    $filetype[$v->id] = $v->name;
                }
                $data['filetype'] = $filetype;
                $data['lastpaly'] = $data1;
                $data['newslice'] = $data2;
                $data['typeslice'] = $data3;
                return json_encode(['errcode' => '1', 'errmsg' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);

            } else {
                return json_encode(['errcode' => '401', 'errmsg' => '该权限不存在'], JSON_UNESCAPED_UNICODE);
            }
        }
//        }else{
//            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
//        }
    }

    //更多视频
    public function moreslice(Request $request){
        $user = \Auth::user();
        if($user){
            $role = $user->role;
            if($role == 1){
                //管理员权限
            }elseif ($role == 2){
                //普通用户权限

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
                    $nowtime = date('Y-m-d');
                    $lasttime = date("Y-m-d",strtotime("-1 day"));
//                    $sql1 = 'SELECT
//                        t1.sliceid,
//                        t1.playnum,
//                        t2.id,
//                        t2.file_title,
//                        t2.file_type,
//                        t2.remark,
//                        t2.filepath,
//                        t2.thumpath,
//                        t2.uid,
//                        t2.username,
//                        t2.status,
//                        t1.addtime
//                        FROM playcollect t1
//                        INNER JOIN uploadlogin t2 ON t1.sliceid = t2.id
//                         WHERE status = 1 AND file_type = ? AND t1.addtime > ? AND t1.addtime < ? ORDER BY t1.playnum DESC LIMIT 0,4';
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
                         WHERE t2.status = 1 AND t2.file_type = ? AND t1.addtime > ? AND t1.addtime < ?  ORDER BY t1.playnum DESC LIMIT 0,4';
                    //$sqlTmp = sprintf($sql,$lasttime,$nowtime);
                    $data1 = DB::select($sql1,[$file_type,$lasttime,$nowtime]);
//                    $sql2 = 'SELECT
//                        id as sliceid,
//                        pvnum as playnum,
//                        id,
//                        file_title,
//                        file_type,
//                        remark,
//                        filepath,
//                        thumpath,
//                        uid,
//                        username,
//                        status,
//                        addtime
//                         FROM uploadlogin WHERE status = 1 AND file_type = %s ORDER BY addtime DESC';
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
                    $data2 = DB::table(DB::raw("($sql2Tmp) as t"))->paginate(20);
                    $data = [];
                    $data['lastpaly'] = $data1;
                    $data['newslice'] = $data2;

                    return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }


            }
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }

    }

    //我的视频
    public function myslice(Request $request){
        $user = \Auth::user();
        if($user){
            $uid = $user->id;
            $sql = 'SELECT
                        id as sliceid,
                        pvnum as playnum,
                        id,
                        file_title,
                        file_type,
                        remark,
                        filepath,
                        thumpath,
                        uid,
                        username,
                        status,
                        addtime
                         FROM uploadlogin WHERE  uid = %s ORDER BY addtime DESC';
            $sqlTmp = sprintf($sql,$uid);
            $list = DB::table(DB::raw("($sqlTmp) as t"))->paginate(12);
            $shenhenum = Uploadlogin::where('uid',$uid)->where('status',2)->count(); //待审核
            $data = [];
            $data['list'] = $list;
            $data['shenhenum'] = $shenhenum;
            return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);

        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //我的收藏
    public function mycollect(Request $request){
        $user = \Auth::user();
        if($user){
            $uid = $user->id;
//            $sql = 'SELECT
//                        t1.sliceid,
//                        t1.userid,
//                        t2.id,
//                        t2.file_title,
//                        t2.file_type,
//                        t2.remark,
//                        t2.filepath,
//                        t2.thumpath,
//                        t2.uid,
//                        t2.username,
//                        t2.status,
//                        t1.addtime
//                        FROM collect t1
//                        INNER JOIN uploadlogin t2 ON t1.sliceid = t2.id
//                         WHERE userid = %s ORDER BY t1.addtime DESC';
            $sql = 'SELECT
                        t1.sliceid,
                        t1.userid,
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
                        FROM (uploadlogin t2 INNER JOIN users t3 ON t2.uid = t3.id)
                        INNER JOIN collect t1 ON t1.sliceid = t2.id
                         WHERE userid = %s ORDER BY t1.addtime DESC';
            $sqlTmp = sprintf($sql,$uid);
            $list = DB::table(DB::raw("($sqlTmp) as t"))->paginate(15);
            $shenhenum = Uploadlogin::where('uid',$uid)->where('status',2)->count(); //待审核
            $data = [];
            $data['list'] = $list;
            $data['shenhenum'] = $shenhenum;
            return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);

        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //取消收藏
    public function cancelcollect(Request $request){
        $user = \Auth::user();
        if($user){
            $uid = $user->id;
            //规则
            try {
                $rules = [
                    'sliceid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'sliceid.required' => '视频id不能为空',
                ];

                $this->validate($request, $rules, $messages);
                $sliceid = $request->sliceid;
                Collect::where('sliceid',$sliceid)->where('userid',$user->id)->delete();
                return json_encode(['errcode'=>'1','errmsg'=>'取消成功'],JSON_UNESCAPED_UNICODE );
            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //历史记录
    public function myhistory(Request $request){
        $user = \Auth::user();
        if($user){
            $uid = $user->id;
//            $sql = 'SELECT
//                        t1.sliceid,
//                        t1.userid,
//                        t2.id,
//                        t2.file_title,
//                        t2.file_type,
//                        t2.remark,
//                        t2.filepath,
//                        t2.thumpath,
//                        t2.uid,
//                        t2.username,
//                        t2.status,
//                        t1.addtime
//                        FROM playhistory t1
//                        INNER JOIN uploadlogin t2 ON t1.sliceid = t2.id
//                         WHERE userid = %s ORDER BY t1.addtime DESC';
            $sql = 'SELECT
                        t1.sliceid,
                        t1.userid,
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
                        INNER JOIN playhistory t1 ON t1.sliceid = t2.id
                         WHERE userid = %s ORDER BY t1.addtime DESC';
            $sqlTmp = sprintf($sql,$uid);
            $list = DB::table(DB::raw("($sqlTmp) as t"))->paginate(15);
            $shenhenum = Uploadlogin::where('uid',$uid)->where('status',2)->count(); //待审核
            $data = [];
            $data['list'] = $list;
            $data['shenhenum'] = $shenhenum;
            return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //全局搜索
    public function search(Request $request){
        $user = \Auth::user();
        if($user){
            $where = ' t2.status = 1 ';
            $search = $request->input('search');
            if($search){
                $where.=' and file_title like "%' . $search . '%"';
            }
//            $sql = "SELECT
//                        id as sliceid,
//                        pvnum as playnum,
//                        id,
//                        file_title,
//                        file_type,
//                        remark,
//                        filepath,
//                        thumpath,
//                        uid,
//                        username,
//                        status,
//                        addtime
//                         FROM uploadlogin WHERE %s ORDER BY addtime DESC" ;
            $sql = 'SELECT
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
                         FROM uploadlogin t2 INNER JOIN users t3 ON t3.id = t2.uid WHERE %s ORDER BY addtime DESC';
            $sqlTmp = sprintf($sql,$where);
            $list = DB::table(DB::raw("($sqlTmp) as t"))->paginate(12);
            $data = [];
            $data['list'] = $list;
            return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //视频类型
    public function filetype(Request $request){
        $typelist = DB::select('SELECT id,name FROM slicetype');;
        $filetype = [];
        foreach ($typelist as $k => $v) {
            $filetype[$v->id] = $v->name;
        }
        return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$filetype]);
    }

}
