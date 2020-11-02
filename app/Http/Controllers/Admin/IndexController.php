<?php

namespace App\Http\Controllers\Admin;

use App\Models\Collect;
use App\Models\Playcollect;
use App\Models\Slicedown;
use App\Models\Uploadlogin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    //
    public function Index(Request $request){
        $user = \Auth::user();
        if($user){
            $role = $user->role;
          if($role == 1){
              $ltime = date('Y-m-d',strtotime('-1 day'));
              $nowtime = date('Y-m-d');
              $qitime = date('Y-m-d',strtotime('-6 day'));
              $lasttime = date('Y-m-d',strtotime('+1 day'));
              //当日视频播放量
              $dayplaynum = Playcollect::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->sum('playnum');
              //当日视频收藏量
              $collectnum = Collect::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();
              //当日视频下载数量
              $downloadnum = Slicedown::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->sum('downnum');
              //当日视频上传数量
              $uploadnum = Uploadlogin::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();
              //当日视频待审核数量
              $auditnum = Uploadlogin::where('status',1)->where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();

              //昨日视频播放量
              $ldayplaynum = Playcollect::where('addtime','>',$ltime)->where('addtime','<',$nowtime)->sum('playnum');
              //当日视频收藏量
              $lcollectnum = Collect::where('addtime','>',$ltime)->where('addtime','<',$nowtime)->count();
              //当日视频下载数量
              $ldownloadnum = Slicedown::where('addtime','>',$ltime)->where('addtime','<',$nowtime)->sum('downnum');
              //当日视频上传数量
              $luploadnum = Uploadlogin::where('addtime','>',$ltime)->where('addtime','<',$nowtime)->count();
              //当日视频待审核数量
              $lauditnum = Uploadlogin::where('status',1)->where('addtime','>',$ltime)->where('addtime','<',$nowtime)->count();

              $ud = [];
              //播放量
              if($dayplaynum >= $ldayplaynum){
                  $ud[0]['status'] = 1;
                  $ud[0]['ud'] = $dayplaynum-$ldayplaynum;
              }else{
                  $ud[0]['status'] = 2;
                  $ud[0]['ud'] = $ldayplaynum-$dayplaynum;
              }
              //收藏量
              if($collectnum >= $lcollectnum){
                  $ud[1]['status'] = 1;
                  $ud[1]['ud'] = $collectnum-$lcollectnum;
              }else{
                  $ud[1]['status'] = 2;
                  $ud[1]['ud'] = $lcollectnum-$collectnum;
              }
              //下载量
              if($downloadnum >= $ldownloadnum){
                  $ud[2]['status'] = 1;
                  $ud[2]['ud'] = $downloadnum-$ldownloadnum;
              }else{
                  $ud[2]['status'] = 2;
                  $ud[2]['ud'] = $ldownloadnum-$downloadnum;
              }
              //视频上传
              if($uploadnum >= $luploadnum){
                  $ud[3]['status'] = 1;
                  $ud[3]['ud'] = $uploadnum-$luploadnum;
              }else{
                  $ud[3]['status'] = 2;
                  $ud[3]['ud'] = $luploadnum-$uploadnum;
              }
              //待审核
              if($auditnum >= $lauditnum){
                  $ud[4]['status'] = 1;
                  $ud[4]['ud'] = $auditnum-$lauditnum;
              }else{
                  $ud[4]['status'] = 2;
                  $ud[4]['ud'] = $lauditnum-$auditnum;
              }


//              $sql = 'select SUM(playnum) as playnum, addtime from (
//                        select playnum , DATE_FORMAT( concat(date(addtime),\' \',floor( hour(addtime)/2 )*2),\'%H:00\')  as addtime from playcollect
//                         where addtime >= CURDATE() and addtime < DATE_ADD(CURDATE(),INTERVAL 1 DAY) ) a
//                        group by addtime
//                        ORDER BY addtime';
              $sql = 'select
                        SUM(playnum) as playsum,
                        SUM(collect) as collectsum,
                        DATE_FORMAT(addtime,\'%Y-%m-%d\') as addtime
                        from playcollect
                        WHERE DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(addtime)
                        GROUP BY DATE_FORMAT(addtime,\'%Y-%m-%d\')';
              $list = DB::select($sql);
              $arrdate = $this->printDates($qitime,$nowtime);
              $weekarray=array("日","一","二","三","四","五","六");

              $arr = [];
              foreach ($arrdate as $k=>$v){
                  $zhou = '周'.$weekarray[date("w",strtotime($v))];
                  $arr[$k]['playnum'] = 0;
                  $arr[$k]['collectnum'] = 0;
                  $arr[$k]['addtime'] = $v;
                  $arr[$k]['zhou'] = $zhou;
                  foreach ($list as $k1=>$v1){
                      if($v1->addtime == $v){
                          $arr[$k]['playnum'] = $v1->playsum;
                          $arr[$k]['collectnum'] = $v1->collectsum;
                      }
                  }
              }
              $sql1 = 'SELECT t1.id,
                                 t1.username ,
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
                                ORDER BY topvnum desc limit 0,10';
              $userlist = DB::select($sql1);
              $data = [];
              $data['dayplaynum'] = $dayplaynum;
              $data['collectnum'] = $collectnum;
              $data['downloadnum'] = $downloadnum;
              $data['uploadnum'] = $uploadnum;
              $data['auditnum'] = $auditnum;
              $data['arr'] = $arr;
              $data['userlist'] = $userlist;
              $data['ud'] = $ud;
              return json_encode(['errcode' => '1', 'errmsg' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
          }elseif($role == 2){
              return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
          }
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }

    public function printDates($start,$end){
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        $arrdate = [];
        while ($dt_start<=$dt_end){
             $arrdate[] = date('Y-m-d',$dt_start);
            $dt_start = strtotime('+1 day',$dt_start);
        }
        return $arrdate;
    }
}
