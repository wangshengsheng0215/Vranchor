<?php

namespace App\Http\Controllers\Admin;

use App\Models\Users;
use App\Service\ImageUploadhandler;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    //获取用户信息
    public function userinfo(Request $request){
        $user = \Auth::user();
        if($user){
            $data = [];
            $data['id'] = $user->id;
            $data['username'] = $user->username;
            $data['name'] = $user->name;
            $data['head_portrait'] = $user->head_portrait;
            $data['logo'] = $user->logo;
            $data['role'] = $user->role == 1 ? '超级管理员':'普通用户';
            $data['mobile'] = $user->mobile;
            return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //修改用户信息
    public function updateinfo(Request $request ,ImageUploadhandler $uploadhandler){
        $user = \Auth::user();
        if($user){
            $data = [];
            $name = $request->input('name');
            $headfilepath = $request->input('headfile');
            $logofilepath = $request->input('logofile');
//            $headfile = $request->file('headfile');
//            $logofile = $request->file('logofile');
//            if($headfile){
//                $result = $uploadhandler->save($headfile,'headport',$user->id);
//                if($result){
//                    $data['headport'] = $result['path'];
//                }
//                $user->head_portrait = strstr($data['headport'],'uploads');
//            }
//            if($logofile){
//                $result1 = $uploadhandler->save($logofile,'logo',$user->id);
//                if($result1){
//                    $data['logo'] = $result1['path'];
//
//                }
//                $user->logo = strstr($data['logo'],'uploads');
//            }
            $user->name = $name;
            if($headfilepath){
                $user->head_portrait = $headfilepath;
            }
            if($logofilepath){
                $user->logo = $logofilepath;
            }
            $user->save();
            return json_encode(['errcode'=>'1','errmsg'=>'更新成功'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //修改密码
    public function updatepassword(Request $request){
        $user = \Auth::user();
        if($user){
            try {
                //规则
                $rules = [
                    'oldpassword'=>'required',
                    'newpassword'=>'required|confirmed',
                ];
                //自定义消息
                $messages = [
                    'oldpassword.required' => '请输入旧密码',
                    'newpassword.required' => '请输入新密码',
                    'newpassword.confirmed' => '两次输入新密码不一致',
                ];

                $this->validate($request, $rules, $messages);

                $oldpassword = $request->input('oldpassword');
                $newpassword = $request->input('newpassword');

                if(Hash::check($oldpassword,$user->password)){
                    $user->password = Hash::make($newpassword);
                    if($user->save()){
                        return json_encode(['errcode'=>'1','errmsg'=>'更新成功'],JSON_UNESCAPED_UNICODE );
                    }
                    return json_encode(['errcode'=>'201','errmsg'=>'更新失败'],JSON_UNESCAPED_UNICODE );
                }else{
                    return json_encode(['errcode'=>'1003','errmsg'=>'旧密码不对'],JSON_UNESCAPED_UNICODE );
                }

            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //用户列表
    public function userlist(Request $request){
        $user = \Auth::user();
        if($user){
            $where = ' 1=1 ';

            $key = $request->input('name');
            if($key != null){
                $where.=' and t1.username like "%' . $key . '%"';
            }
            $sql = 'SELECT t1.id,t1.username ,
                     t1.head_portrait,
                     t1.name,
                     t1.mobile,
                     t1.role,
                     t1.addtime,
                     t1.`status`,
                     IFNULL(t2.topvnum ,0) topvnum,
                     IFNULL(t2.touvnum ,0) touvnum,
                     IFNULL(t2.touidnum,0) touidnum
                    FROM users t1
                    LEFT JOIN
                    (SELECT SUM(pvnum) as topvnum , SUM(uvnum) as touvnum, COUNT(uid) as touidnum ,uid FROM uploadlogin GROUP BY uid) t2
                    ON t1.id = t2.uid
                    where %s
                    ORDER BY t1.id';
            $sqlTmp = sprintf($sql,$where);
            //dd($sqlTmp);
            $list = DB::table(DB::raw("($sqlTmp) as t"))->paginate(10);

            return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$list]);
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }


    //修改其他用户信息
    public function updateuserinfo(Request $request,ImageUploadhandler $uploadhandler){
        $user = \Auth::user();
        if($user){
            $data = [];
            $id = $request->input('uid');
            $name = $request->input('name');
            $headfilepath = $request->input('headfile');
            $logofilepath = $request->input('logofile');
//            $headfile = $request->file('headfile');
//            $logofile = $request->file('logofile');
//            if($headfile){
//                $result = $uploadhandler->save($headfile,'headport',$user->id);
//                if($result){
//                    $data['headport'] = $result['path'];
//                }
//                $user->head_portrait = strstr($data['headport'],'uploads');
//            }
//            if($logofile){
//                $result1 = $uploadhandler->save($logofile,'logo',$user->id);
//                if($result1){
//                    $data['logo'] = $result1['path'];
//
//                }
//                $user->logo = strstr($data['logo'],'uploads');
//            }
            $data['name'] = $name;
            if($headfilepath){
                //$user->head_portrait = $headfilepath;
                $data['head_portrait'] = $headfilepath;
            }
            if($logofilepath){
                //$user->logo = $logofilepath;
                $data['logo'] = $logofilepath;
            }
            Users::where('id',$id)->update($data);
            //$user->save();
            return json_encode(['errcode'=>'1','errmsg'=>'更新成功'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }


}
