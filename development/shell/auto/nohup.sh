#!/usr/bin/env bash
#
#  +------------------------------------------------------------------------+
#  | nohub日志分割
#  +------------------------------------------------------------------------+
#

# 文件日期
current_date=`date -d "-1 day" "+%Y%m%d"`

# 分割
split -b 65535000 -d -a 4 ./nohup.out   ./logs/nohup_${current_date}_

# 清空
cat /dev/null > nohup.out
