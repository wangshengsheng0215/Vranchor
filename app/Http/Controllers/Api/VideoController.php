<?php

namespace App\Http\Controllers\Api;

use App\Models\Collect;
use App\Models\Playcollect;
use App\Models\Playhistory;
use App\Models\Slicedown;
use App\Models\Uploadlogin;
use App\Service\ImageUploadhandler;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VideoController extends Controller
{


    //视频上传
    public function sliceUpload(Request $request)
    {


        $user = \Auth::user();
        if($user){
        try {
            //规则
            $rules = [
                'file'=>'required',
                'blob_num'=>'required',
                'total_blob_num'=>'required',
                'file_name'=>'required',
                'file_title'=>'required',
                'file_type'=>'required',
                'remark'=>'required',
            ];
            //自定义消息
            $messages = [
                'file.required' => '视频文件不能为空',
                'file_title.required' => '视频文件标题不能为空',
                'file_type.required' => '视频文件分类不能为空',
                'blob_num.required' => '文件当前分片数不能为空',
                'total_blob_num.required' => '文件分片总数不能为空',
                'file_name.required' => '文件名不能为空',
                'remark.required' => '视频描述不为空'
            ];
            $this->validate($request, $rules, $messages);

            $file = $request->file('file');
            $blob_num = $request->get('blob_num');
            $total_blob_num = $request->get('total_blob_num');
            $file_name = $request->get('file_name');
            $realPath = $file->getRealPath(); //临时文件的绝对路径
            $filesize = $file->getSize();
            $remark = $request->get('remark');
            $file_title = $request->get('file_title');
            $file_type = $request->get('file_type');

//            $mpfile = $request->file('mp');
//            $mprealPath = $mpfile->getRealPath();
//            $mpfile_name = $mpfile->getClientOriginalName();
//            $mppath = 'slice/'.date('Ynd');
//            $mpfilename = $mppath.'/'.$mpfile_name;
//            $mpup = Storage::disk('local')->put($mpfilename,file_get_contents($mprealPath));

            // 存储地址
            $path = 'slice/'.date('Ymd')  ;
            $filename = $path .'/'. $file_name . '_' . $blob_num;

            //上传
            $upload = Storage::disk('local')->put($filename, file_get_contents($realPath));
//            $mpblob = Storage::disk('local')->get($path.'/'.$mpfile_name);
//            $this->file_force_contents(public_path('uploads').'/'.$path.'/'.$mpfile_name,$mpblob,FILE_APPEND);
//            Storage::disk('local')->delete($path.'/'.$mpfile_name);
            //判断是否是最后一块，如果是则进行文件合成并且删除文件块
            if($blob_num == $total_blob_num){
                for($i=1; $i<= $total_blob_num; $i++){
                    $blob = Storage::disk('local')->get($path.'/'. $file_name.'_'.$i);
                    //Storage::disk('admin')->append($path.'/'.$file_name, $blob);   //不能用这个方法，函数会往已经存在的文件里添加0X0A，也就是\n换行符
                    $this->file_force_contents(public_path('uploads').'/'.$path.'/'.$file_name,$blob,FILE_APPEND);


                }
                //合并完删除文件块
                for($i=1; $i<= $total_blob_num; $i++){
                    Storage::disk('local')->delete($path.'/'. $file_name.'_'.$i);
                }

                //生成缩略图
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries'  => 'C:/phpstudy_pro/Extensions/php/php7.3.4nts/ffmpeg-N-99404-g56ff01e6ec-win64-gpl-shared/bin/ffmpeg.exe',
                    'ffprobe.binaries' => 'C:/phpstudy_pro/Extensions/php/php7.3.4nts/ffmpeg-N-99404-g56ff01e6ec-win64-gpl-shared/bin/ffprobe.exe',
                    'timeout'          => 3600, // The timeout for the underlying process
                    'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
                ]);
                $video = $ffmpeg->open(public_path('uploads').'/'.$path.'/'.$file_name);
                $img_dir = base_path('public/uploads/video/'.$file_name.'_res.jpg');
                $video->frame(TimeCode::fromSeconds(1))->save($img_dir);
//                $advan = $ffmpeg->openAdvanced(array(public_path('uploads').'/'.$path.'/'.$file_name));
//                $advan->map(array('0:a'),new Mp3(),public_path('uploads').'/'.$path.'/'.$mpfile_name)
//                    ->save();
            }
            if ($upload){
                //$user = \Auth::user();
                $uploadlogin = new Uploadlogin();
                $uploadlogin->filename = $file_name;
                $uploadlogin->filepath = 'uploads/'.$path.'/'.$file_name;
                $uploadlogin->uid = $user->id;
                $uploadlogin->username = $user->username;
                $uploadlogin->thumname = $file_name.'_res.jpg';
                $uploadlogin->thumpath = 'uploads/video/'.$file_name.'_res.jpg';
                $uploadlogin->status = 2;
                $uploadlogin->remark = $remark;
                $uploadlogin->slicesize = $this->sizecount($filesize);
                $uploadlogin->pvnum = 0;
                $uploadlogin->uvnum = 0;
                $uploadlogin->file_title = $file_title;
                $uploadlogin->file_type = $file_type;
                if ($uploadlogin->save()){
                    return json_encode(['errcode'=>1,'errmsg'=>"上传成功"],JSON_UNESCAPED_UNICODE);
                }
                return json_encode(['errcode'=>0,'errmsg'=>"保存记录失败"],JSON_UNESCAPED_UNICODE);


            }else{
                return json_encode(['errcode'=>0,'errmsg'=>"上传失败"],JSON_UNESCAPED_UNICODE);
            }

        }catch (ValidationException $validationException){
            $messages = $validationException->validator->getMessageBag()->first();
            return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
        }
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }

    }

    function sizecount($filesize) {
        if($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' gb';
        } elseif($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' mb';
        } elseif($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' kb';
        } else {
            $filesize = $filesize . ' bytes';
        }
        return $filesize;
    }


    public function file_force_contents( $fullPath, $contents, $flags = 0 )
    {
        $parts = explode( '/', $fullPath );
        array_pop( $parts );
        $dir = implode( '/', $parts );
        if( !is_dir( $dir ) )
            mkdir( $dir, 0777, true );

        file_put_contents( $fullPath, $contents, $flags );
    }


    //文件下载
    public function sliceDownload(Request $request)
    {

        $user = \Auth::user();
//        if($user){
            try {
                //规则
                $rules = [
                    'sliceid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'sliceid.required' => '视频id不能为空',
                ];
                $this->validate($request, $rules, $messages);
                $sliceid = $request->input('sliceid');
                $slice = Uploadlogin::where('id',$sliceid)->first();
                if ($slice){
                    $path = $slice->filepath;
                    //$path = $request->input('path');
                    $filename = strstr($path,'slice');
                    //获取文件资源
                    $file = Storage::disk('uploads')->readStream($filename);
                    //获取文件大小
                    $fileSize = Storage::disk('uploads')->size($filename);
                    header("Content-type:application/octet-stream");//设定header头为下载
                    header("Accept-Ranges:bytes");
                    header("Accept-Length:".$fileSize);//响应大小
                    header("Content-Disposition: attachment; filename=$filename");//文件名

                    //不设置的话要等缓冲区满之后才会响应
                    ob_end_clean();//缓冲区结束
                    ob_implicit_flush();//强制每当有输出的时候,即刻把输出发送到浏览器\
                    header('X-Accel-Buffering: no'); // 不缓冲数据

                    $limit=1024*1024;
                    $count=0;

                    //限制每秒的速率
                    while($fileSize-$count>0){//循环读取文件数据
                        $data=fread($file,$limit);
                        $count+=$limit;
                        echo $data;//输出文件
                        //ob_flush();//增加的.
                        //flush();     //增加的
                        sleep(1);
                    }
                    $slicedown = new Slicedown();
                    $nowtime = date('Y-m-d');
                    $lasttime = date("Y-m-d",strtotime("+1 day"));
                    if(Slicedown::where('sliceid',$sliceid)->where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->first()){
                        if(Slicedown::where('sliceid',$sliceid)->increment('downnum')){
                           // Uploadlogin::where('id',$sliceid)->increment('pvnum');
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一成功"],JSON_UNESCAPED_UNICODE);
                        }else{
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一失败"],JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        $slicedown->sliceid = $sliceid;
                        $slicedown->downnum = 1;;
                        $slicedown->year = date('Y');
                        $slicedown->month = date('m');
                        $slicedown->day = date('d');

                        if ($slicedown->save()){
                            //Uploadlogin::where('id',$sliceid)->increment('pvnum');
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一成功"],JSON_UNESCAPED_UNICODE);
                        }
                        return json_encode(['errcode'=>1,'errmsg'=>"播放量增一失败"],JSON_UNESCAPED_UNICODE);
                    }
                }

            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }
//        }else{
//            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
//        }
    }

    //视频播放
    public function sliceplay(Request $request){
        $user = \Auth::user();
//        if ($user){
            try {
                //规则
                $rules = [
                    'sliceid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'sliceid.required' => '视频id不能为空',
                ];
                $this->validate($request, $rules, $messages);
                $sliceid = $request->input('sliceid');
                if($user){
                    if(Playhistory::where('sliceid',$sliceid)->where('userid',$user->id)->first()){
                        $a = Playhistory::where('sliceid',$sliceid)->where('userid',$user->id)->update(['addtime'=>date('Y-m-d H:i:s')]);
                    }else{
                        $history = new Playhistory();
                        $history->sliceid = $sliceid;
                        $history->userid = $user->id;
                        $history->year = date('Y');
                        $history->month = date('m');
                        $history->day = date('d');
                        $a = $history->save();
                    }
                }else{
                    if(Playhistory::where('sliceid',$sliceid)->first()){
                        $a = Playhistory::where('sliceid',$sliceid)->update(['addtime'=>date('Y-m-d H:i:s')]);
                    }else{
                        $history = new Playhistory();
                        $history->sliceid = $sliceid;
                        $history->userid = $user->id;
                        $history->year = date('Y');
                        $history->month = date('m');
                        $history->day = date('d');
                        $a = $history->save();
                    }
                }


                if ($a){
                    $playcollect = new Playcollect();
                    $nowtime = date('Y-m-d');
                    $lasttime = date("Y-m-d",strtotime("+1 day"));
                    if(Playcollect::where('sliceid',$sliceid)->where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->first()){
                        if(Playcollect::where('sliceid',$sliceid)->increment('playnum')){
                            Uploadlogin::where('id',$sliceid)->increment('pvnum');
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一成功"],JSON_UNESCAPED_UNICODE);
                        }else{
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一失败"],JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        $playcollect->sliceid = $sliceid;
                        $playcollect->playnum = 1;
                        $playcollect->collect = 0;
                        $playcollect->year = date('Y');
                        $playcollect->month = date('m');
                        $playcollect->day = date('d');

                        if ($playcollect->save()){
                            Uploadlogin::where('id',$sliceid)->increment('pvnum');
                            return json_encode(['errcode'=>1,'errmsg'=>"播放量增一成功"],JSON_UNESCAPED_UNICODE);
                        }
                        return json_encode(['errcode'=>1,'errmsg'=>"播放量增一失败"],JSON_UNESCAPED_UNICODE);
                    }
                }




            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

//        }else{
//            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
//        }
    }


    //视频收藏
    public function slicecollect(Request $request){
        $user = \Auth::user();
        if ($user){
            try {
                //规则
                $rules = [
                    'sliceid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'sliceid.required' => '视频id不能为空',
                ];
                $this->validate($request, $rules, $messages);
                $sliceid = $request->input('sliceid');
                if (Collect::where('sliceid',$sliceid)->where('userid',$user->id)->first()){
                     return json_encode(['errcode'=>1006,'errmsg'=>"该视频你已收藏"],JSON_UNESCAPED_UNICODE);
                }
                $collect = new Collect();
                $collect->sliceid = $sliceid;
                $collect->userid = $user->id;
                $collect->year = date('Y');
                $collect->month = date('m');
                $collect->day = date('d');
                if($collect->save()){
                        Uploadlogin::where('id',$sliceid)->increment('uvnum');
                        $playcollect = new Playcollect();
                        $nowtime = date('Y-m-d');
                        $lasttime = date("Y-m-d",strtotime("+1 day"));
                        if(Playcollect::where('sliceid',$sliceid)->where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->first()){
                            if(Playcollect::where('sliceid',$sliceid)->increment('collect')){
                                return json_encode(['errcode'=>1,'errmsg'=>"收藏成功"],JSON_UNESCAPED_UNICODE);
                            }else{
                                return json_encode(['errcode'=>1008,'errmsg'=>"收藏量增一失败"],JSON_UNESCAPED_UNICODE);
                            }
                        }else{
                            $playcollect->sliceid = $sliceid;
                            $playcollect->playnum = 0;
                            $playcollect->collect = 1;
                            $playcollect->year = date('Y');
                            $playcollect->month = date('m');
                            $playcollect->day = date('d');

                            if ($playcollect->save()){
                                Uploadlogin::where('id',$sliceid)->increment('uvnum');
                                return json_encode(['errcode'=>1,'errmsg'=>"收藏成功"],JSON_UNESCAPED_UNICODE);
                            }
                            return json_encode(['errcode'=>1008,'errmsg'=>"播放量增一失败"],JSON_UNESCAPED_UNICODE);
                        }
                }else{
                    return json_encode(['errcode'=>1008,'errmsg'=>"收藏失败"],JSON_UNESCAPED_UNICODE);
                }




            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    //视频信息
    public function sliceinfo(Request $request){
        $user = \Auth::user();
//        if ($user){
            try {
                //规则
                $rules = [
                    'sliceid'=>'required',
                ];
                //自定义消息
                $messages = [
                    'sliceid.required' => '视频id不能为空',
                ];
                $this->validate($request, $rules, $messages);
                $sliceid = $request->input('sliceid');
//                $sql = 'SELECT
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
//                         FROM uploadlogin WHERE id = ? ';
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
                        t2.status,
						t3.head_portrait,
                        t2.addtime
                         FROM uploadlogin t2  INNER JOIN users t3 ON t3.id = t2.uid  WHERE t2.id = ?';
                $info = DB::select($sql,[$sliceid]);
                if ($user){
                    $collcet = Collect::where('sliceid',$sliceid)->where('userid',$user->id)->first();
                }
                $data = [];
                $data['list'] = $info;
                if($collcet){
                    $data['collect'] = 1;
                }else{
                    $data['collect'] = 2;
                }
                return json_encode(['errcode' => '1', 'errmsg' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
            }catch (ValidationException $validationException){
                $messages = $validationException->validator->getMessageBag()->first();
                return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
            }

//        }else{
//            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
//        }
    }

    //相关推荐
    public function slicerecommend(Request $request){
        $user = \Auth::user();
        if($user){
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
                         FROM uploadlogin WHERE status = 1 AND file_type = ? ORDER BY addtime DESC limit 0,5';
                    $data2 = DB::select($sql2,[$file_type]);
                    $data = [];
                    $data['slicerecommend'] = $data2;

                    return  json_encode(['errcode'=>'1','errmsg'=>'ok','data'=>$data],JSON_UNESCAPED_UNICODE);
                }catch (ValidationException $validationException){
                    $messages = $validationException->validator->getMessageBag()->first();
                    return json_encode(['errcode'=>'1001','errmsg'=>$messages],JSON_UNESCAPED_UNICODE );
                }

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


    //文件上传
    public function fileuploads(Request $request,ImageUploadhandler $uploadhandler){
        $user = \Auth::user();
        $fname = $request->input('fname');
        if($fname == 'headfile'){
            $headfile = $request->file('headfile');
            $result = $uploadhandler->save($headfile,'headport',$user->id);
            if($result){
                $data['headport'] = $result['path'];
            }
            $path = strstr($data['headport'],'uploads');
            return json_encode(['errcode'=>'1','errmsg'=>'上传成功','data'=>$path],JSON_UNESCAPED_UNICODE );

        }elseif ($fname == 'logofile'){
            $logofile = $request->file('logofile');
            $result1 = $uploadhandler->save($logofile,'logo',$user->id);
            if($result1){
                $data['logo'] = $result1['path'];

            }
            $path = strstr($data['logo'],'uploads');
            return json_encode(['errcode'=>'1','errmsg'=>'上传成功','data'=>$path],JSON_UNESCAPED_UNICODE );
        }
        return json_encode(['errcode'=>'1','errmsg'=>'上传失败'],JSON_UNESCAPED_UNICODE );

    }





}
