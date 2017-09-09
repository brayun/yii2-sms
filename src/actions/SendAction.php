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

namespace brayun\sms\actions;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use yii\base\Action;
use yii\helpers\Json;

/**
 * 发送短信
 * Class SendAction
 * @package brayun\sms\SendAction
 */
class SendAction extends Action
{
    /** @var string 短信签名 */
    public $signName = '';

    /** @var string 短信模板id */
    public $tplCode = '';

    public $params = [];

    /** @var string 手机号码字段名称 */
    public $phoneField = 'mobile';

    /** @var string 短信缓存前掇 */
    public $cachePrefix = '__SMS__';

    /** @var string  aliyun id */
    public $accessKeyId;

    public $accessKeySecret;

    /** @var int 验证码过期时间 默认2小时有效 */
    public $expiration = 60 * 120;

    /** @var int 每天发送短信次数限制 */
    public $dateSendLimit = 5;

    /** @var string 验证码参数字段 */
    public $smsCodeField = 'code';

    public $beforeFunction;

    public function run()
    {
        if (\Yii::$app->request->post($this->phoneField)) {
            if (is_callable($this->beforeFunction)) {
                if ($res = call_user_func($this->beforeFunction)) {
                    return $res;
                }
            }
            return $this->sendSms(\Yii::$app->request->post($this->phoneField));
        }
        return [
            'code' => 1,
            'msg' => '请输入手机号码'
        ];
    }

    /**
     * 发送短信
     * @param $phone
     * @return array
     */
    private function sendSms($phone)
    {
        $dateRecord = $this->cachePrefix.$phone.date('Ymd');
        if (\Yii::$app->cache->get($dateRecord) >= $this->dateSendLimit) {
            return [
                'code' => 1,
                'msg' => "每天最多发送{$this->dateSendLimit}次,请明天再发"
            ];
        }

        Config::load();
        $profile = DefaultProfile::getProfile('cn-hangzhou', $this->accessKeyId ? : \Yii::$app->params['aliyun']['accessKeyId'], $this->accessKeySecret ? : \Yii::$app->params['aliyun']['accessKeySecret']);
        DefaultProfile::addEndpoint('cn-hangzhou', 'cn-hangzhou', 'Dysmsapi', 'dysmsapi.aliyuncs.com');
        $client = new DefaultAcsClient($profile);
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($phone);
        $request->setSignName($this->signName);
        $request->setTemplateCode($this->tplCode);
        $request->setTemplateParam(Json::encode($this->params));
        $res = $client->getAcsResponse($request);
        if (isset($res->Code) && $res->Code == 'OK') {
            /** 记录短信验证码值 */
            \Yii::$app->cache->set($this->cachePrefix.$phone, $this->params[$this->smsCodeField], $this->expiration);
            /** 记录当天验证码发送次数 */
            if (\Yii::$app->cache->exists($dateRecord)) {
                \Yii::$app->cache->set($dateRecord, \Yii::$app->cache->get($dateRecord) + 1,strtotime(date('Y-m-d 23:59:59')) - time());
            } else {
                \Yii::$app->cache->set($dateRecord, 1, strtotime(date('Y-m-d 23:59:59')) - time());
            }

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