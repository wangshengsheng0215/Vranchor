<?php


namespace App\Service\Sms;
use Illuminate\Support\Facades\Cache;

class Sms
{
    public static function send($param)
    {
        $phone = $param['mobile'];
        $code = $param['code'];
        $query =  [
            'PhoneNumbers' => $phone,
            'SignName' => $param['sms_sign'],
            'TemplateCode' => $param['sms_tpl_code'],
            'TemplateParam' => json_encode(['code' => $code]),
        ];
        $result = (new SendSms())->send($query);
        self::cacheCode($phone,$code);
        return $result;
    }
    protected static function cacheCode($phone,$code){
        $key =  $phone;
        Cache::put($key,$code,5);
        return true;
    }
    //检测验证码是否正确
    public static function checkCode($phone,$code)
    {
        $key =  $phone;
        //dd(Cache::get($phone));
        if(!Cache::has($key)){
            return ['code'=>'1009','msg'=>'验证码已过期'];
        }
        //$cacheCode =  Cache::pull($phone);
        $cacheCode = Cache::get($phone);
        if($cacheCode == $code){
            return true;
        }else{
            return ['code'=>'2001','msg'=>'验证码不一致'];
        }
    }
}
