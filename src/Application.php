<?php
/*
 *          ┌─┐       ┌─┐
 *       ┌──┘ ┴───────┘ ┴──┐
 *       │                 │
 *       │       ───       │
 *       │  ─┬┘       └┬─  │
 *       │                 │
 *       │       ─┴─       │
 *       └───┐         ┌───┘
 *           │         └──────────────┐
 *           │                        ├─┐
 *           │                        ┌─┘
 *           │                        │
 *           └─┐  ┐  ┌───────┬──┐  ┌──┘
 *             │ ─┤ ─┤       │ ─┤ ─┤
 *             └──┴──┘       └──┴──┘
 *        @Author Ethan <ethan@brayun.com>
 */

namespace brayun\sms;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use yii\base\Component;
use yii\helpers\Json;

class Application extends Component
{

    public $accessKeyId;

    public $accessKeySecret;

    public $signName = '';

    public function send($phone,$tplCode, $params = [], $signName = '')
    {
        Config::load();
        $profile = DefaultProfile::getProfile('cn-hangzhou', $this->accessKeyId ? : \Yii::$app->params['aliyun']['accessKeyId'], $this->accessKeySecret ? : \Yii::$app->params['aliyun']['accessKeySecret']);
        DefaultProfile::addEndpoint('cn-hangzhou', 'cn-hangzhou', 'Dysmsapi', 'dysmsapi.aliyuncs.com');
        $client = new DefaultAcsClient($profile);
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($phone);
        $request->setSignName($signName ? :$this->signName);
        $request->setTemplateCode($tplCode);
        if ($params) {
            $request->setTemplateParam(Json::encode($params));
        }
        $res = $client->getAcsResponse($request);
        if (isset($res->Code) && $res->Code == 'OK') {
            return [
                'code' => 0,
                'msg' => '发送成功'
            ];
        }
        return [
            'code' => 1,
            'msg' => '发送失败'
        ];
    }
}
