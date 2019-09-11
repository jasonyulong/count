#!/usr/bin/env bash

#  -----------------------------------
#  本脚本为所有平台拉单队列里待处理订单自动规则
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
	# 当前时间
	nowtime=$(date +%H:%M)
	# 预利润
	grepName="order -m profit -a confirm --platform="
	# 过期时间 秒, 超过这个时间的进程直接干掉, 处理死进程
	timeOut=3600

	# 所有平台
	platformArray=("ebay" "aliexpress" "wish" "amazon" "cdiscount" "priceminister" "walmart" "joom" "linio" "lazada" "shopee" "jumia" "fnac" "manomano" "mymall" "wadi")

	# 遍历平台开始运行
	for platform in ${platformArray[@]}
	do
		# 根据关键词查找已运行进程数量,小于1个进程则可运行
		count=`ps -ef | grep "$grepName$platform" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			echo ${nowtime}": start run $grepName$platform, count:"${count}
			$phpBin $phpRun $grepName$platform > /dev/null 2>&1 &
		else
			echo ${nowtime}": $grepName$platform is run,count:"${count}
		fi
	done

	# 休息一会
	sleep 3600
done
