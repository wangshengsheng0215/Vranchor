<?php


namespace App\Service\Sms;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class SendSms
{
    //初始化短信接口
    public function init()
    {
        $key = env('AibabaSMSAcceccKey');//这个不用我教了吧。。
        $secret = env('AibabaSMSAccessKeySecret');//这个不用我教了吧。。
        AlibabaCloud::accessKeyClient($key,$secret)->asDefaultClient();
    }

    /***
     * PhoneNumbers:手机号
     * SignName:签名
     * TemplateCode:模板码
     * TemplateParam:参数
     ***/
    public function send($query)
    {
        $this->init();
        try {
            $result = AlibabaCloud::rpc()
                ->regionId('cn-hangzhou')
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => $query,
                ])
                ->request();
            $respone = $result->toArray();
            Log::info('sendInfo:'.json_encode($respone));
            return  $respone;
        } catch (ClientException $exception) {
            $error = $exception->getErrorMessage();
            Log::info('smsError:'.json_encode($error));
            return ['Code'=>'1001','Message'=>$error];
            //return false;
        } catch (ServerException $exception) {
            $error = $exception->getErrorMessage();
            Log::info('smsError:'.json_encode($error));
            return ['Code'=>'1001','Message'=>$error];
            //return false;
        }
    }
}
