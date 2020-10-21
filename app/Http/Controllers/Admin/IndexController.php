<?php

namespace App\Http\Controllers\Admin;

use App\Models\Collect;
use App\Models\Playcollect;
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
              $nowtime = date('Y-m-d');
              $lasttime = date('Y-m-d',strtotime('+1 day'));
              //当日视频播放量
              $dayplaynum = Playcollect::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->sum('playnum');
              //当日视频收藏量
              $collectnum = Collect::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();
              //当日视频下载数量
              $downloadnum = 0;
              //当日视频上传数量
              $uploadnum = Uploadlogin::where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();
              //当日视频待审核数量
              $auditnum = Uploadlogin::where('status',1)->where('addtime','>',$nowtime)->where('addtime','<',$lasttime)->count();


              $sql = 'select SUM(playnum) as playnum, addtime from (
                        select playnum , DATE_FORMAT( concat(date(addtime),\' \',floor( hour(addtime)/2 )*2),\'%H:00\')  as addtime from playcollect
                         where addtime >= CURDATE() and addtime < DATE_ADD(CURDATE(),INTERVAL 1 DAY) ) a
                        group by addtime
                        ORDER BY addtime';
              $list = DB::select($sql);
              $arr = [
                  ['playnum'=>'0','addtime'=>'00:00'],
                  ['playnum'=>'0','addtime'=>'02:00'],
                  ['playnum'=>'0','addtime'=>'04:00'],
                  ['playnum'=>'0','addtime'=>'06:00'],
                  ['playnum'=>'0','addtime'=>'08:00'],
                  ['playnum'=>'0','addtime'=>'10:00'],
                  ['playnum'=>'0','addtime'=>'12:00'],
                  ['playnum'=>'0','addtime'=>'14:00'],
                  ['playnum'=>'0','addtime'=>'16:00'],
                  ['playnum'=>'0','addtime'=>'18:00'],
                  ['playnum'=>'0','addtime'=>'20:00'],
                  ['playnum'=>'0','addtime'=>'22:00'],
              ];
              if ($list){
                  foreach ($list as $k=>$v){
                      switch ($v->addtime){
                          case '00:00':
                              $arr['0']['playnum'] = $v->playnum;
                              break;
                          case '02:00':
                              $arr['1']['playnum'] = $v->playnum;
                              break;
                          case '04:00':
                              $arr['2']['playnum'] = $v->playnum;
                              break;
                          case '06:00':
                              $arr['3']['playnum'] = $v->playnum;
                              break;
                          case '08:00':
                              $arr['4']['playnum'] = $v->playnum;
                              break;
                          case '10:00':
                              $arr['5']['playnum'] = $v->playnum;
                              break;
                          case '12:00':
                              $arr['6']['playnum'] = $v->playnum;
                              break;
                          case '14:00':
                              $arr['7']['playnum'] = $v->playnum;
                              break;
                          case '16:00':
                              $arr['8']['playnum'] = $v->playnum;
                              break;
                          case '18:00':
                              $arr['9']['playnum'] = $v->playnum;
                              break;
                          case '20:00':
                              $arr['10']['playnum'] = $v->playnum;
                              break;
                          case '22:00':
                              $arr['11']['playnum'] = $v->playnum;
                              break;
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
              return json_encode(['errcode' => '1', 'errmsg' => 'ok', 'data' => $data], JSON_UNESCAPED_UNICODE);
          }elseif($role == 2){
              return json_encode(['errcode'=>'2','errmsg'=>'不是管理员'],JSON_UNESCAPED_UNICODE );
          }
        }else{
            return json_encode(['errcode'=>'402','errmsg'=>'token已过期请替换'],JSON_UNESCAPED_UNICODE );
        }
    }
}
