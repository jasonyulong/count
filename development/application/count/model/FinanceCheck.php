<?php


namespace app\count\model;
use think\Model;

/**
 * 统计抽查
 * Class FinanceCheck
 * @package app\count\model
 */
class FinanceCheck extends Model
{
    protected $name = 'finance_check';
    public $connection = 'count';
}