<?php


namespace App\Http\Controllers\Web;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function list(Request $request){
       // return view('homepage');
        @$name = 'slice';
        $dirname = $name;

        $all = $this->read_all_dir("uploads/" . $dirname);
        if(isset($all['dir'])){
            foreach ($all['dir'] as $k=>$v){
                @$return[] = $v['file'];
            }
            $files = [];
            array_walk_recursive($return, function ($x) use (&$files) {
                $files[] = $x;
            });
        }else{
            @$files = $all['file'];
        }

        return view('list',['files'=>$files,'dirname'=>$dirname]);
    }
    public function read_all_dir($dir) {

        if (!is_dir($dir)) {
            echo "<script>alert('未找到该文件夹,请重新输入!')</script>";
            die;
        }
        $result = array();
        @$handle = opendir($dir);
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($cur_path)) {
                        $result['dir'][$cur_path] = $this->read_all_dir($cur_path);
                    } else {
                        $result['file'][] = $cur_path;
                        $result['files'][] = $file;
                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }

    public function test(Request $request){
          return view('test');
      }

    /**
     * @Desc: 切片上传
     *
     * @param Request $request
     * @return mixed
     */
    public function sliceUpload(Request $request)
    {
        $file = $request->file('file');
        $blob_num = $request->get('blob_num');
        $total_blob_num = $request->get('total_blob_num');
        $file_name = $request->get('file_name');
        $realPath = $file->getRealPath(); //临时文件的绝对路径
        // 存储地址
        $path = 'slice/'.date('Ymd')  ;
        $filename = $path .'/'. $file_name . '_' . $blob_num;

        //上传
        $upload = Storage::disk('local')->put($filename, file_get_contents($realPath));

        //判断是否是最后一块，如果是则进行文件合成并且删除文件块
        if($blob_num == $total_blob_num){
            for($i=1; $i<= $total_blob_num; $i++){
                $blob = Storage::disk('local')->get($path.'/'. $file_name.'_'.$i);
//              Storage::disk('admin')->append($path.'/'.$file_name, $blob);   //不能用这个方法，函数会往已经存在的文件里添加0X0A，也就是\n换行符
                $this->file_force_contents(public_path('uploads').'/'.$path.'/'.$file_name,$blob,FILE_APPEND);


            }
            //合并完删除文件块
            for($i=1; $i<= $total_blob_num; $i++){
                Storage::disk('local')->delete($path.'/'. $file_name.'_'.$i);
            }
        }

        if ($upload){
            return json_encode(['code'=>1,'msg'=>"上传成功"],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(['code'=>0,'msg'=>"上传失败"],JSON_UNESCAPED_UNICODE);
        }

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
