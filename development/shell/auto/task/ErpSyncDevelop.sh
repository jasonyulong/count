#!/usr/bin/env bash

#  -----------------------------------
#  本脚本为更新开发员报表
#  author  Kevin
# -----------------------------------

#引入配置文件
. ./config

while true;
do
    # 开发员销售额
    run_action "develop -m Sale -a sync" #> /dev/null 2>&1 &

    # 开发员开发SKU
    run_action "develop -m Product -a sync" #> /dev/null 2>&1 &

    # 休息一会
    sleep 900
done
