<?php

namespace app\count\model;

use think\Model;

class OrderStatus extends Model
{
    protected $name = 'order_status';
    public $connection = 'count';
}
