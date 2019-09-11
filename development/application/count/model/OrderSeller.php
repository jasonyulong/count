<?php

namespace app\count\model;

use think\Model;

class OrderSeller extends Model
{
    protected $name = 'order_seller';
    public $connection = 'count';
}
