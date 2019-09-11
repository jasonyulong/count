#!/usr/bin/env bash

#  -----------------------------------
#  此脚本为同步ERP数据到统计系统来
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
	# 当前时间
	nowtime=$(date +%H:%M)
	# 运行路由方法
	grepName="sync -m order -a times"
	# 退款订单路由
	refundGrepName="sync -m order -a refundtimes"
	# 过期时间 秒, 超过这个时间的进程直接干掉, 处理死进程
	timeOut=1800
	# 发货时间
	scantime="scantime"
	# 确认利润时间
	updateprofittime="updateprofittime"
	# 退款时间
	#refundtime="refundtime"
	# 进回收站时间
	canceltime="canceltime"

	# 所有平台
	platformArray=("ebay" "aliexpress" "wish" "amazon" "cdiscount" "priceminister" "walmart" "joom" "linio" "lazada" "shopee" "jumia" "fnac" "manomano" "mymall" "wadi")

	# 遍历平台开始运行
	for platform in ${platformArray[@]}
	do
		# 如果超时则干掉进程
		killTimeoutPlatform "$grepName" $platform $timeOut

		# 根据进系统时间更新
		count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			$phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
		fi

		# 根据发货时间更新
		count=`ps -ef | grep "$grepName\ $platform\ $scantime" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			$phpBin $phpRun $grepName $platform $scantime > /dev/null 2>&1 &
		fi

		# 根据确认利润时间更新
		count=`ps -ef | grep "$grepName\ $platform\ $updateprofittime" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			$phpBin $phpRun $grepName $platform $updateprofittime > /dev/null 2>&1 &
		fi

		# 根据退款时间更新
		#count=`ps -ef | grep "$grepName\ $platform\ $refundtime" | grep -v "grep" | wc -l`
		#if [[ $count -le 0 ]]; then
		#	$phpBin $phpRun $grepName $platform $refundtime > /dev/null 2>&1 &
		#fi

		# 根据进回收站时间更新
		count=`ps -ef | grep "$grepName\ $platform\ $canceltime" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			$phpBin $phpRun $grepName $platform $canceltime > /dev/null 2>&1 &
		fi

		# 根据退款时间更新
		count=`ps -ef | grep "$refundGrepName\ $platform" | grep -v "grep" | wc -l`
		if [[ $count -le 0 ]]; then
			$phpBin $phpRun $refundGrepName $platform > /dev/null 2>&1 &
		fi
	done

	# 休息时间
	sleep 1500
done
