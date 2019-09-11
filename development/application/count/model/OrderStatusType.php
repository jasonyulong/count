<?php

namespace app\count\model;

use think\Model;

class OrderStatusType extends Model
{
    protected $name = 'order_status_type';
    public $connection = 'count';
}
