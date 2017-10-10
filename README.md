<p align="center">
    <a href="https://www.brayun.com/" target="_blank">
        <img src="https://www.brayun.com/img/touch/chrome-touch-icon-192x192.png" width="200" alt="上海柏锐网络科技有限公司" />
    </a>
</p>

<p align="center">
  <a href="https://www.brayun.com">上海柏锐网络科技有限公司</a>是一家技术型服务企业.可提供专业的技术外包服务,定制OA,ERP,电商系统,H5网站,天猫淘宝H5,欢迎前来咨询
</p>


## 安装

```shell
composer require brayun/yii2-sms
```


## 示例 Controller 添加actions
```PHP
public function actions()
{
    return [
        'smscode' => [
            'class' => SendAction::className(),
            'tplCode' => 'SMS_94663333',
            'signName' => '我是签名',
            'params' => [
                'code' => rand(1000, 9999)
            ],
            'beforeFunction' => function () {
                if (User::findOne(['mobile' => \Yii::$app->request->post('mobile')])) {
                    return [
                        'code' => 1,
                        'msg' => '手机号码已存在!'
                    ];
                }
            }
        ]
    ];
}

```

## 示例Model
```PHP
public function rules()
{
    return [
        ...
        ['code', PhoneCodeValidator::className()],
    ];
}
```

## 组件使用示例 main.php components下添加
```PHP
'components' => [
    'sms' => [
        'class' => 'brayun\sms\Application',
        'signName' => '统一签名'
    ],
    ...
]
```
### 组件使用
```PHP
Yii::$app->sms->send('13000000000','SMS_123456',['code'=>1234], '这里签名可另写');
```
