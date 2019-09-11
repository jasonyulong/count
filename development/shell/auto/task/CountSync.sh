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
	nowtime=$(date +%M)
	if [[ $nowtime -le 10 ]]; then
	    # 统计今天更新的订单的销量
        run_action "order -m Order -a countSale --start="$(date -d '3 day ago' +%Y-%m-%d)" --end="$(date +%Y-%m-%d) > /dev/null 2>&1 &

        # 统计今天更新的订单的订单状态
        run_action  "order -m Order -a countStatus --start="$(date -d '3 day ago' +%Y-%m-%d)" --end="$(date +%Y-%m-%d) > /dev/null 2>&1 &
    else
        # 统计今天更新的订单的销量
        run_action "order -m Order -a countSale" > /dev/null 2>&1 &

        # 统计今天更新的订单的订单状态
        run_action  "order -m Order -a countStatus" > /dev/null 2>&1 &
	fi

	# 当月销售额平均值
	run_action "order -m Order -a countMonthlySaleAll --month-num=2" > /dev/null 2>&1 &

    	# 统计收支数据   分钟、非必填(20)  开始日期、非必填(2018-09-25)  结束日期、非必填(2018-09-30)
	run_action "finance -m funds -a sync" > /dev/null 2>&1 &

	# 统计售后数据   分钟、非必填(20)  开始日期、非必填(2018-09-25)  结束日期、非必填(2018-09-30)
	run_action "finance -m refund -a sync" > /dev/null 2>&1 &

    	# 物流报表   开始日期、非必填(2018-09-25)  结束日期、非必填(2018-09-30)
	run_action "transport -m bill -a sync" > /dev/null 2>&1 &
	
	# 同步ERP用户到COUNT
	run_action "common -m UserCli -a syncUserToAdmin" > /dev/null 2>&1 &	

	# 休息一会
	sleep 1800
done
