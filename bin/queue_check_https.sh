#!/bin/bash

php_script="/usr/local/bin/php71"
php_command="/projects/otus-queue/src/CheckHttpsQueue.php"
pid_file="/tmp/CheckHttpsQueue.pid"

pid=$(touch ${pid_file} && cat ${pid_file})

if [ "$pid" == "" -o "$(ps -p $(cat ${pid_file}) | wc -l)" -eq 1 ]
then {
	# run daemon
	${php_script} -f ${php_command} &> /dev/null & echo $! > ${pid_file}
	echo "$php_command - script restart"
	exit 1
}
else
{
    # daemon already running
	exit 0
}
fi