#!/usr/bin/env bash

#  -----------------------------------
#  本脚本为处理订单队列里需要执行的任务
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
	# 当前时间
	nowtime=$(date +%H:%M)
	# 运行路由方法
	grepName="order -m Order -a runQueue"

    # 所有对列
	queueArray=("status" "sale" "skuSale")
    # 遍历平台开始运行
	for queue in ${queueArray[@]}
	do
		# 根据关键词查找已运行进程数量,小于1个进程则可运行
		count=`ps -ef | grep "$grepName\ --queue=$queue" | grep -v "grep" | wc -l`
		if [[ $queue -eq "skuSale" ]]; then
			total=1
		else
			total=5
		fi
		if [[ $count -le $total ]]; then
			$phpBin $phpRun $grepName --queue=$queue > /dev/null 2>&1 &
		fi
	done

	# 休息一会
	sleep 3
done
