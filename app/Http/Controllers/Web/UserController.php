<?php

namespace App\Http\Controllers\Web;

use App\Models\Collect;
use App\Models\Uploadlogin;
use App\Models\Users;
use App\Service\ImageUploadhandler;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
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

    //用户信息
    public function userinfo(Request $request){
        $user = \Auth::user();
        if($user){
            $data = [];
            $data['id'] = $user->id;
            $data['username'] = $user->username;
            $data['name'] = $user->name;
            $data['head_portrait'] = $user->head_portrait;
            $data['mobile'] = $user->mobile;
            //视频上传数
            $data['upnum'] = Uploadlogin::where('uid',$user->id)->count();
            $data['collcetnum'] = Collect::where('userid',$user->id)->count();
            $data['daishenhenum'] = Uploadlogin::where('status',2)->where('uid',$user->id)->count();
            //视频收藏数
            //视频待审核数
            return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
        }else{
           return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    public function orderuserinfo(Request $request){
        $user = \Auth::user();
        if($user){
            //规则
            try {
                $rules = [
                    'uid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'uid.required' => '用户id不能为空',
                ];

                $this->validate($request, $rules, $messages);
                $uid = $request->uid;
                $user1 = Users::where('id',$uid)->first();
                $data = [];
                $data['id'] = $user1->id;
                $data['username'] = $user1->username;
                $data['name'] = $user1->name;
                $data['head_portrait'] = $user1->head_portrait;
                $data['mobile'] = $user1->mobile;
                //视频上传数
                $data['upnum'] = Uploadlogin::where('uid',$user1->id)->count();
                $data['collcetnum'] = Collect::where('userid',$user1->id)->count();
                $data['daishenhenum'] = Uploadlogin::where('status',2)->where('uid',$user1->id)->count();
                //视频收藏数
                //视频待审核数
                return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);

            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

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
           $headfile = $request->file('headfile');
           if($headfile){
              $result = $uploadhandler->save($headfile,'headport',$user->id);
              if($result){
                  $data['headport'] = $result['path'];
              }
               $user->head_portrait = strstr($data['headport'],'uploads');
           }

           $user->name = $name;
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
}
