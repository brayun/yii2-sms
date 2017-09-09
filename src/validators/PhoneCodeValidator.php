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

namespace brayun\sms\validators;


use yii\validators\Validator;

/**
 * 手机号码验证
 * Class PhoneValidator
 * @package brayun\skeleton\validators
 */
class PhoneCodeValidator extends Validator
{

    /** @var string 手机号码字段 */
    public $phoneField = 'mobile';

    /** @var string 验证码查询时的缓存前掇 */
    public $cachePrefix = '__SMS__';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = \Yii::t('yii', '{attribute}不正确');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @return bool
     */
    public function validateAttribute($model, $attribute)
    {
        if (\Yii::$app->cache->get($this->cachePrefix.$model->{$this->phoneField}) === $model->{$attribute}) {
            return true;
        }
        $this->addError($model, $attribute, $this->message);
    }


}