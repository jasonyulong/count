<?php

namespace app\count\model;

use think\Model;

class SkuStore extends Model
{
    protected $name = 'sku_store';
    public $connection = 'count';
}
