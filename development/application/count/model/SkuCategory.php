<?php

namespace app\count\model;

use think\Model;

class SkuCategory extends Model
{
    protected $name = 'sku_category';
    public $connection = 'count';
}
