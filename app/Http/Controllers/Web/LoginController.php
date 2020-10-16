<?php

namespace App\Http\Controllers\Web;

use App\Models\Users;
use App\Service\Sms\Sms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    //注册
    public function register(Request $request){
        try {
            //规则 加了code
            $rules = [
                'username'=>'required|unique:users,username',
                'password'=>'required',
                'mobile'=>'required|unique:users,mobile|regex:/^1[345789][0-9]{9}$/',
                'code'=>'required'

            ];
            //自定义消息
            $messages = [
                'username.required' => '请输入用户名',
                'password.required' => '请输入密码',
                'username.unique' => '用户名已存在',
                'mobile.required' => '请输入手机号',
                'mobile.unique' => '手机号已被使用',
                'mobile.regex' => '手机号不合法',
                'code.rqquired' => '验证码不能为空'
            ];

            $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $password = $request->input('password');
            $mobile = $request->input('mobile');
            $code = $request->input('code');
            $checkcode = Sms::checkCode($mobile,$code);
            if(is_array($checkcode)){
                return json_encode($checkcode,JSON_UNESCAPED_UNICODE);
            }


            $user = new Users();
            $user->username = $username;
            $user->password =  Hash::make($password);
            $user->mobile = $mobile;
            $user->status = 1;
            $user->role = 2;//默认为普通用户
            $a = $user->save();
            if($a){
                return json_encode(['errcode'=>1,'errmsg'=>'ok']);
            }else{
                return json_encode(['errcode'=>'2002','errmsg'=>'error']);
            }

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
    }
}
