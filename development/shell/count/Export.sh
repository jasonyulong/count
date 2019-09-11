#!/usr/bin/env bash

#  -----------------------------------
#  此脚本为数据导出
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

# 运行路由方法
grepName="order -m order -a runOrderExportTask"
# 过期时间 秒, 超过这个时间的进程直接干掉, 处理死进程
timeOut=3600
#超时干掉
killTimeoutPid $grepName $timeOut

# 根据进系统时间同步
count=`ps -ef | grep "$grepName" | grep -v "grep" | wc -l`
if [[ $count -le 0 ]]; then
    $phpBin $phpRun $grepName > /dev/null 2>&1 &
fi