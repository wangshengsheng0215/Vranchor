<?php

namespace App\Http\Controllers\Admin;

use App\Models\Uploadlogin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class VideoController extends Controller
{
    //
    //视频列表
    public function slicelist(Request $request){
        $user = \Auth::user();
        if($user){
            $where = ' 1=1 ';
            $key = $request->input('name');
            if($key != null){
                $where.=' and file_title like "%' . $key . '%"';
            }
            $paginate = $request->input('paginate');
            if(empty($paginate)){
                $paginate = 10;
            }
            $list = Uploadlogin::whereRaw($where)->orderBy('addtime','desc')->paginate($paginate);
            foreach ($list as $v){
                if($v->status == 2){
                    $v->statuscn = '待审核';
                }elseif ($v->status == 1){
                    $v->statuscn = '上架';
                }elseif ($v->status == 3){
                    $v->statuscn = '下架';
                }
            }
            return json_encode(['errcode'=>1,'errmsg'=>'ok','data'=>$list]);
        }else{
           return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //修改状态 及 上架下架审核
    public function updatestatus(Request $request){
        $user = \Auth::user();
        if($user){
            if($user->role == 1){
                try {
                    //规则
                    $rules = [
                        'sliceid'=>'required',
                        'status'=>'required',
                    ];
                    //自定义消息
                    $messages = [
                        'sliceid.required' => '视频id不为空',
                        'status.required' => '状态不为空',

                    ];

                    $this->validate($request, $rules, $messages);
                    $sliceid = $request->input('sliceid');
                    $status = $request->input('status');  //通过即为上架，不通过即为下架
                    //通过传1 不通过传3 下架传3 上架传1
                    Uploadlogin::where('id',$sliceid)->update(['status'=>$status]);
                    return json_encode(['errcode'=>'1','errmsg'=>'更新成功'],JSON_UNESCAPED_UNICODE );

                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }

            }
            return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //批量修改状态
    public function batchupdatestatus(Request $request){
        $user = \Auth::user();
        if($user){
            if($user->role == 1){
                try {
                    //规则
                    $rules = [
                        'sliceid'=>'required',
                        'status'=>'required',
                    ];
                    //自定义消息
                    $messages = [
                        'sliceid.required' => '视频id不为空',
                        'status.required' => '状态不为空',

                    ];

                    $this->validate($request, $rules, $messages);
                    $arrsliceid = $request->input('sliceid');
                    $status = $request->input('status');  //通过即为上架，不通过即为下架
                    //通过传1 不通过传3 下架传3 上架传1
                    Uploadlogin::whereIn('id',$arrsliceid)->update(['status'=>$status]);
                    return json_encode(['errcode'=>'1','errmsg'=>'更新成功'],JSON_UNESCAPED_UNICODE );

                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }

            }
            return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }


    //删除
    public function deleteslice(Request $request){
        $user = \Auth::user();
        if($user){
            if($user->role == 1){
                try {
                    //规则
                    $rules = [
                        'sliceid'=>'required',
                    ];
                    //自定义消息
                    $messages = [
                        'sliceid.required' => '视频id不为空',
                    ];

                    $this->validate($request, $rules, $messages);
                    $sliceid = $request->input('sliceid');
                    if (Uploadlogin::where('id',$sliceid)->delete()){
                        return json_encode(['errcode'=>'1','errmsg'=>'删除成功'],JSON_UNESCAPED_UNICODE );
                    }else{
                        return json_encode(['errcode'=>'1002','errmsg'=>'删除失败'],JSON_UNESCAPED_UNICODE );
                    }


                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }
            }
            return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //批量删除
    public function batchdeleteslice(Request $request){
        $user = \Auth::user();
        if($user){
            if($user->role == 1){
                try {
                    //规则
                    $rules = [
                        'sliceid'=>'required',
                    ];
                    //自定义消息
                    $messages = [
                        'sliceid.required' => '视频id不为空',
                    ];

                    $this->validate($request, $rules, $messages);
                    $arrsliceid = $request->input('sliceid');
                    if (Uploadlogin::whereIn('id',$arrsliceid)->delete()){
                        return json_encode(['errcode'=>'1','errmsg'=>'删除成功'],JSON_UNESCAPED_UNICODE );
                    }else{
                        return json_encode(['errcode'=>'1002','errmsg'=>'删除失败'],JSON_UNESCAPED_UNICODE );
                    }


                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }
            }
            return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }
}
