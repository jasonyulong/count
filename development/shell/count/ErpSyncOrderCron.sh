#!/usr/bin/env bash

#  -----------------------------------
#  此脚本为同步ERP数据到统计系统来，统计当天数据
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

days=$1
# 当前时间
nowtime=$(date +%Y-%m-%d)
if [[ $days -lt 0 ]]; then
    nowtime=$(date +%Y-%m-%d -d "$1 days")
fi
# 运行路由方法
grepName="sync -m order -a times"
# 过期时间 秒, 超过这个时间的进程直接干掉, 处理死进程
timeOut=1800
#进系统时间
addtime="ebay_addtime"
# 发货时间
scantime="scantime"
# 确认利润时间
updateprofittime="updateprofittime"
# 退款时间
refundtime="refundtime"
# 进回收站时间
canceltime="canceltime"
# 所有平台
platformArray=("ebay" "aliexpress" "wish" "amazon" "cdiscount" "priceminister" "walmart" "joom" "linio" "lazada" "shopee" "jumia" "fnac" "manomano" "mymall" "wadi")
# 遍历平台开始运行
for platform in ${platformArray[@]}
do
    # 根据进系统时间同步
    count=`ps -ef | grep "$grepName\ $platform\ $addtime" | grep -v "grep" | wc -l`
    if [[ $count -le 0 ]]; then
        $phpBin $phpRun $grepName $platform $addtime $nowtime > /dev/null 2>&1 &
    fi
    # 根据扫描时间同步
    count=`ps -ef | grep "$grepName\ $platform\ $scantime" | grep -v "grep" | wc -l`
    if [[ $count -le 0 ]]; then
        $phpBin $phpRun $grepName $platform $scantime $nowtime > /dev/null 2>&1 &
    fi
    # 根据确认利润时间同步
    count=`ps -ef | grep "$grepName\ $platform\ $updateprofittime" | grep -v "grep" | wc -l`
    if [[ $count -le 0 ]]; then
        $phpBin $phpRun $grepName $platform $updateprofittime $nowtime > /dev/null 2>&1 &
    fi
    # 根据退款时间同步
    count=`ps -ef | grep "$grepName\ $platform\ $refundtime" | grep -v "grep" | wc -l`
    if [[ $count -le 0 ]]; then
        $phpBin $phpRun $grepName $platform $refundtime $nowtime > /dev/null 2>&1 &
    fi
    # 根据作废时间同步
    count=`ps -ef | grep "$grepName\ $platform\ $canceltime" | grep -v "grep" | wc -l`
    if [[ $count -le 0 ]]; then
        $phpBin $phpRun $grepName $platform $canceltime $nowtime > /dev/null 2>&1 &
    fi
done