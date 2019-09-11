<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\admin\validate;

use think\Validate;

/**
 * 订单表校验
 * Class Order
 * @package app\count\validate
 */
class OrderValidate extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'platform'         => 'require|max:50',
        'platform_account' => 'require',
        'recordnumber'     => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [];

    /**
     * 字段描述
     */
    protected $field = [];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['platform', 'platform_account', 'recordnumber'],
        'edit' => ['platform', 'platform_account', 'recordnumber'],
    ];

    /**
     * Admin constructor.
     * @param array $rules
     * @param array $message
     * @param array $field
     */
    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'platform'         => __('Platform'),
            'platform_account' => __('Platform_account'),
            'recordnumber'     => __('recordnumber'),
        ];
        parent::__construct($rules, $message, $field);
    }

}
