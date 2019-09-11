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

	# 所有平台
	platformArray=("ebay" "aliexpress" "wish" "amazon" "cdiscount" "priceminister" "walmart" "joom" "linio" "lazada" "shopee" "jumia" "fnac" "manomano" "mymall" "wadi")

	# 遍历平台开始运行
	for platform in ${platformArray[@]}
	do
        # 新进系统订单
	    grepName="sync -m order -a pull"
		# 根据关键词查找已运行进程数量,小于1个进程则可运行
		count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
		if [[ $count -le 20 ]]; then
			$phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
		fi

        # 等待分配订单
	    grepName="sync -m order -a rules"
		# 根据关键词查找已运行进程数量,小于1个进程则可运行
		count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
		if [[ $count -le 20 ]]; then
			$phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
		fi

		# 已发货订单
	    grepName="sync -m order -a shipped"
		# 根据关键词查找已运行进程数量,小于1个进程则可运行
		count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
		if [[ $count -le 20 ]]; then
			$phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
		fi

		# 确认利润订单
        grepName="sync -m order -a finish"
        # 根据关键词查找已运行进程数量,小于1个进程则可运行
        count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
        if [[ $count -le 20 ]]; then
            $phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
        fi

        # 售后订单
        grepName="sync -m order -a refund"
        # 根据关键词查找已运行进程数量,小于1个进程则可运行
        count=`ps -ef | grep "$grepName\ $platform" | grep -v "grep" | wc -l`
        if [[ $count -le 20 ]]; then
            $phpBin $phpRun $grepName $platform > /dev/null 2>&1 &
        fi
	done

	# 最新更新订单
	grepName="sync -m order -a changes"
    # 根据关键词查找已运行进程数量,小于1个进程则可运行
    count=`ps -ef | grep "$grepName" | grep -v "grep" | wc -l`
    if [[ $count -le 20 ]]; then
        $phpBin $phpRun $grepName > /dev/null 2>&1 &
    fi

	# 进回收站订单
    grepName="sync -m order -a recycle"
    # 根据关键词查找已运行进程数量,小于1个进程则可运行
    count=`ps -ef | grep "$grepName" | grep -v "grep" | wc -l`
    if [[ $count -le 20 ]]; then
        $phpBin $phpRun $grepName > /dev/null 2>&1 &
    fi

	# 休息时间
	sleep 10
done
