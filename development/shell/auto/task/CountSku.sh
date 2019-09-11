#!/usr/bin/env bash

#  -----------------------------------
#  本脚本为更新sku销量
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
    	# 更新的当天发货订单的进系统时间销量
	run_action "sku -m SkuSync -a sync" #> /dev/null 2>&1 &

	# 休息一会
	sleep 3600
done
