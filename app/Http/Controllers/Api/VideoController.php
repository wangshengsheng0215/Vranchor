<?php

namespace App\Http\Controllers\Api;

use App\Models\Uploadlogin;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
                'remark'=>'required',
            ];
            //自定义消息
            $messages = [
                'file.required' => '视频文件不能为空',
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


            // 存储地址
            $path = 'slice/'.date('Ymd')  ;
            $filename = $path .'/'. $file_name . '_' . $blob_num;

            //上传
            $upload = Storage::disk('local')->put($filename, file_get_contents($realPath));

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
                    'ffmpeg.binaries'  => 'D:/phpstudy_pro/Extensions/php/php7.3.4nts/ffmpeg-N-99404-g56ff01e6ec-win64-gpl-shared/bin/ffmpeg.exe',
                    'ffprobe.binaries' => 'D:/phpstudy_pro/Extensions/php/php7.3.4nts/ffmpeg-N-99404-g56ff01e6ec-win64-gpl-shared/bin/ffprobe.exe',
                    'timeout'          => 3600, // The timeout for the underlying process
                    'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
                ]);
                $video = $ffmpeg->open(public_path('uploads').'/'.$path.'/'.$file_name);
                $img_dir = base_path('public/uploads/video/'.$file_name.'_res.jpg');
                $video->frame(TimeCode::fromSeconds(1))->save($img_dir);
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
                $uploadlogin->status = 1;
                $uploadlogin->remark = $remark;
                $uploadlogin->slicesize = $this->sizecount($filesize);
                $uploadlogin->pvnum = 0;
                $uploadlogin->uvnum = 0;
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
    public function sliceDownload()
    {

        $path = 'slice/'.date('Ymd') ;

        $filename = $path .'/'. '02248b1d5b1de297b64d747135688fcc.mp4' ;
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

    }
}
