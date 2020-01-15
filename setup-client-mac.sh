#!/bin/bash

#检查系统环境中是否有可用的程序
checkfn(){
	# check if fn is already checked
	eval "chk=\"\$$1\""
	if [ ! -z "$chk" ]; then
		return 0
	fi
	for fn in "$@"
	do
		ret=`(which $fn) 2>/dev/null`
		if [ ! -z "$ret" ]; then
			eval "$1=\"$ret\""
			return 0
		fi
	done
	echo "[ERROR]$fn not found."
	return 1
}

checkfn wget || exit 1
checkfn php || exit 1
checkfn frpc || exit 1
checkfn md5sum md5 || exit 1
md5sum=${md5sum:-${md5}}
checkfn awk || exit 1
checkfn chmod || exit 1
checkfn sed || exit 1
checkfn dirname || exit 1
checkfn cp || exit 1

if [ ! $2 ]; then
    echo "$0 <token> <server_addr> [<serverport> <admin_port>]"
    exit 1
fi

curdir=$(cd "$(${dirname} "$0")";pwd)
startsh=${curdir}/start-client-mac.sh
frpctrl=${curdir}/config.php

FRP_TOKEN=$1
FRP_SERVER_ADDR=$2
FRP_SERVER_PORT=${3:-443}
FRP_ADMIN_PORT=${4:-7500}

${cp} ${curdir}/dependents/frpc.ini ${curdir}/frpc.ini

echo make start script $startsh ...

echo '#!/bin/sh' > ${startsh} \
    && echo export FRP_SERVER_ADDR="${FRP_SERVER_ADDR:-0.0.0.0}" >> ${startsh} \
    && echo export FRP_SERVER_PORT="${FRP_SERVER_PORT:-443}" >> ${startsh} \
    && echo export FRP_TOKEN="`echo ${FRP_TOKEN}basefrptoken | ${md5sum} | ${awk} '{print $1}'`" >> ${startsh} \
    && echo export FRP_ADMIN_NAME="`echo ${FRP_TOKEN}clientadminname | ${md5sum} | ${awk} '{print $1}'`" >> ${startsh} \
    && echo export FRP_ADMIN_PORT="${FRP_ADMIN_PORT:-7500}" >> ${startsh} \
    && echo export FRP_ADMIN_TOKEN="`echo ${FRP_TOKEN}clientadmintoken | ${md5sum} | ${awk} '{print $1}'`" >> ${startsh} \
    && echo "${frpc} -c ${curdir}/frpc.ini " >> ${startsh} \
    && ${chmod} +x ${startsh}

if [ ! -f "${frpctrl}" ]; then
    echo update settings in config.php ...
    echo '<?php' > ${frpctrl} \
        && echo >> ${frpctrl} \
        && echo "define('FRP_TOKEN','${FRP_TOKEN}');" >> ${frpctrl} \
        && echo "define('FRP_SERVER_ADDR','${FRP_SERVER_ADDR}');" >> ${frpctrl} \
        && echo "define('FRP_SERVER_PORT','${FRP_SERVER_PORT}');" >> ${frpctrl} \
        && echo >> ${frpctrl} \
        && echo "\$mappings = [" >> ${frpctrl} \
        && echo "        //server_ip,server_port,local_port" >> ${frpctrl} \
        && echo "        //['10.21.0.88',6379,6379]," >> ${frpctrl} \
        && echo "];" >> ${frpctrl}
else
	echo skip make config.php
fi

