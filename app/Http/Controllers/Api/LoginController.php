<?php

namespace App\Http\Controllers\Api;

use App\Models\Loginhistory;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    //注册接口
    public function register(Request $request){

        try {
            //规则
            $rules = [
                'username'=>'required|unique:users,username',
                'password'=>'required',
                'mobile'=>'required|unique:users,mobile|regex:/^1[345789][0-9]{9}$/',

            ];
            //自定义消息
            $messages = [
                'username.required' => '请输入用户名',
                'password.required' => '请输入密码',
                'username.unique' => '用户名已存在',
                'mobile.required' => '请输入手机号',
                'mobile.unique' => '手机号已被使用',
                'mobile.regex' => '手机号不合法'
            ];

            $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $password = $request->input('password');
            $mobile = $request->input('mobile');


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

    //登录接口
    public function login(Request $request){
        try {
            //规则
            $rules = [
                'username'=>'required',
                'password'=>'required',
            ];

            //自定义消息
            $messages = [
                'username.required' => '请输入用户名/手机号',
                'password.required' => '请输入密码',
            ];

            //$this->validate($request, $rules, $messages);
            $params = $this->validate($request, $rules, $messages);

            $username = $request->input('username');
            $password = $request->input('password');

            $user = Users::where('username',$username)->orwhere('mobile',$username)->first();

            if($user){
                if ($token = Auth::guard('api')->attempt($params)){
                    //登录记录
                    //$ip = $request->ip();
                    $ip = $request->getClientIp();
                    $history = new Loginhistory();
                    $history->userid = $user->id;
                    $history->username = $username;
                    $history->ip = $ip;
                    $a = $history->save();
                    if($a){
                        Session::put('user',$user);
                        Session::put('loginstatus',true);
                        $messages = "登录成功！";
                        $data = [];
                        $data['id'] = $user->id;
                        $data['username'] = $user->username;
                        $data['role'] = $user->role;
                        $data['token'] = 'bearer ' . $token;
                        return json_encode(['errcode'=>'1','errmsg'=>$messages,'data'=>$data],JSON_UNESCAPED_UNICODE );
                    }else{
                        $messages = "登录记录失败！";
                        return json_encode(['errcode'=>'1004','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                    }
                }elseif( $token = Auth::guard('api')->attempt(['mobile'=>$username,'password'=>$password])){
                    //登录记录
                    //$ip = $request->ip();
                    $ip = $request->getClientIp();
                    $history = new Loginhistory();
                    $history->userid = $user->id;
                    $history->username = $username;
                    $history->ip = $ip;
                    $a = $history->save();
                    if($a){
                        Session::put('user',$user);
                        Session::put('loginstatus',true);
                        $messages = "登录成功！";
                        $data = [];
                        $data['id'] = $user->id;
                        $data['username'] = $user->username;
                        $data['role'] = $user->role;
                        $data['token'] = 'bearer ' . $token;
                        return json_encode(['errcode'=>'1','errmsg'=>$messages,'data'=>$data],JSON_UNESCAPED_UNICODE );
                    }else{
                        $messages = "登录记录失败！";
                        return json_encode(['errcode'=>'1004','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                    }
                }else{
                    $messages = "密码错误！";
                    return json_encode(['errcode'=>'1003','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }
            }else{
                $messages = "用户不存在请注册！";
                return json_encode(['errcode'=>'1002','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }
        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }


    }



}
