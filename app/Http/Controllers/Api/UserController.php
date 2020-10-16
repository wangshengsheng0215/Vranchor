<?php

namespace App\Http\Controllers\Api;

use App\Models\Smslog;
use App\Models\Users;
use App\Service\Sms\Sms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    //
    //获取用户列表
    public function userlist(Request $request){

        $user = Session::get('user');
        $role = $user->role;
        if($role == '1'){
            $userlist = Users::where('status',1)->paginate(20);
        }elseif($role == '2'){
            $userlist = Users::where('username',$user->username)->paginate(20);
        }
        return json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$userlist],JSON_UNESCAPED_UNICODE );


    }

    //发送验证码

    public function sendcode(Request $request){
        try {
            //规则
            $rules = [
                'mobile'=>'required|regex:/^1[345789][0-9]{9}$/',
            ];
            //自定义消息
            $messages = [
                'mobile.required' => '请输入手机号',
                'mobile.regex' => '手机号不合法',
            ];

            $this->validate($request, $rules, $messages);
            //获取用户和手机号
            $mobile = $request->input('mobile');
            $user = Users::where('mobile',$mobile)->where('status',1)->first();
            if(!isset($user->mobile)){
                return json_encode(['errcode'=>'1002','errmsg'=>'用户手机号不存在'],JSON_UNESCAPED_UNICODE );
            }
            if(!($user->mobile == $mobile)){
                return json_encode(['errcode'=>'1002','errmsg'=>'手机号不一致'],JSON_UNESCAPED_UNICODE );
            }

            $smslog = (new Smslog())->sendMobileVerifyCode($user->mobile);
            return $smslog;

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }

    }

    //修改密码
    public function findPassword(Request $request){

        try {
            //规则
            $rules = [
                'username'=>'required',
                'mobile'=>'required',
                'code'=>'required',
                'newPassword'=>'required',
                'cfmPassword'=>'required'
            ];
            //自定义消息
            $messages = [
                'mobile.required' => '请输入手机号',
                'code.required' => '验证码不为空',
                'newPassword.required' => '请输入新密码',
                'cfmPassword.required' => '请输入确认密码',
            ];
            $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $mobile = $request->input('mobile');
            $code = $request->input('code');
            $newPassword = $request->input('newPassword');
            $cfmPassword = $request->input('cfmPassword');
            if(!($newPassword == $cfmPassword)){
                return json_encode(['errcode'=>'1002','errmsg'=>'密码不一致'],JSON_UNESCAPED_UNICODE );
            }
            $user = Users::where('username',$username)->where('status',1)->first();

            if(!isset($user->mobile)){
                return json_encode(['errcode'=>'1002','errmsg'=>'用户手机号不存在'],JSON_UNESCAPED_UNICODE );
            }
            if(!($user->mobile == $mobile)){
                return json_encode(['errcode'=>'1002','errmsg'=>'账号对应手机号不一致'],JSON_UNESCAPED_UNICODE );
            }
            $checkcode = Sms::checkCode($mobile,$code);
            if(is_array($checkcode)){
                return json_encode($checkcode,JSON_UNESCAPED_UNICODE);
            }

            $user->password = Hash::make($newPassword);
            if ($user->save()){
                Cache::pull($mobile);
                return json_encode(['errcode'=>'1','errmsg'=>'ok'],JSON_UNESCAPED_UNICODE );
            }else{
                return json_encode(['errcode'=>'1005','errmsg'=>'修改失败'],JSON_UNESCAPED_UNICODE );
            }



        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }



}
