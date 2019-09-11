#!/usr/bin/env bash

#  -----------------------------------
#  本脚本为更新sku信息
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
    # 更新的当天发生变化的sku
    run_action "common -m GoodsCli -a sync" #> /dev/null 2>&1 &

    # 更新的当天发生变化的sku
    run_action "common -m GoodsCli -a sync --type=develop" #> /dev/null 2>&1 &

    # 更新最近10天添加的组合SKU
    run_action  "common -m GoodsCli -a sync --type=combine --start="$(date -d '1 day ago' +%Y-%m-%d)" > /dev/null 2>&1 &

	# 休息一会
	sleep 3600
done
