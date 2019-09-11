#!/usr/bin/env bash
#
#  +------------------------------------------------------------------------+
#  | 检测系统执行过久的PHP进程
#  +------------------------------------------------------------------------+
#

wordsArr=("/usr/local/moji/php/bin/php" "/usr/bin/curl")
# 超时时长
timeOut=1800

while true;
do
	# 遍历平台开始运行
	for grepName in ${wordsArr[@]}
	do
		# 当前时间
		nowtime=$(date +%H:%M)
		# 找出已经运行的进程
		pro="$(ps -ef |grep "$grepName"|sort -k3,3|head -n1)"
		# 获取开始执行时间
		strtime="$(echo $pro|awk '{print $5}')"
		# 获取进程ID
		pid="$(echo $pro|awk '{print $2}')"
		# 计算运行时长 秒
		runtime=$(($(date -d "$nowtime" +%s) - $(date -d "$strtime" +%s)))
		echo "$grepName =$pid"
		# 判断如果进程运行时间超过设置时间则干掉进程
		if [[ $runtime -gt $timeOut ]]; then
			# kill $pid
			echo "$grepName timeout, kill $pid"
		fi
	done
	# 休息一分钟
	sleep 60
done
