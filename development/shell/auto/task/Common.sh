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
	# 过期时间 秒, 超过这个时间的进程直接干掉, 处理死进程
	timeOut=3600

	# 同步所有平台, 帐号
	run_action "common -m accountCli -a platformAccount" > /dev/null 2>&1 &

	# 同步所有的订单状态
	run_action "common -m systemCli -a orderstatus" > /dev/null 2>&1 &

    # 同步所有仓库
	run_action "common -m systemCli -a store" > /dev/null 2>&1 &

	# 同步所有汇率
	run_action "common -m systemCli -a rate" > /dev/null 2>&1 &

    # 同步所有物流公司
	run_action "common -m systemCli -a company" > /dev/null 2>&1 &

	# 同步所有物流渠道
	run_action "common -m systemCli -a carrier" > /dev/null 2>&1 &

	# 同步所有erp用户,组织架构
	run_action "common -m systemCli -a organization" > /dev/null 2>&1 &

	# 同步所有国家和二字码
	run_action "sync -m common -a countries" > /dev/null 2>&1 &

    # 同步所有商品分类
	run_action "sync -m common -a goodscategory" > /dev/null 2>&1 &

	# 同步ERP用户到COUNT
	#run_action "common -m UserCli -a syncUserToAdmin" > /dev/null 2>&1 &

	# 同步组织架构信息
	run_action "common -m UserCli -a orgPropAndOrgEbay" > /dev/null 2>&1 &

	# 休息一会
	sleep 3600
done
