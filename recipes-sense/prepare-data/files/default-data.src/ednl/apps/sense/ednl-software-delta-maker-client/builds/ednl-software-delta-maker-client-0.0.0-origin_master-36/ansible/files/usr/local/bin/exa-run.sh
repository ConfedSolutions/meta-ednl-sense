#!/bin/bash

exa_error() {
    echo "${1}" >&2
}

exa_run_init() {
	if [ "X${HOME}X" == "XX" ]; then
		HOME=`echo "cd ~${USER}; pwd" | sh`
	fi
	if [ "X${HOME}X" == "XX" ]; then
		exa_error "ERR: variable HOME undefined"
    	exit 100
	fi
	if [ ! -d ${HOME}/.exa-run ]; then
		mkdir ${HOME}/.exa-run
	fi
	if [ ! -d ${HOME}/.exa-run/pids ]; then
		mkdir ${HOME}/.exa-run/pids
	fi
}

exa_run_start() {
	if [ "$3" == "" ]; then
		exa_run_usage
	fi

	#tests if nice value is within range
	if [[ $4 -gt 20 ]] || [[ $4 -lt -19 ]]; then
		echo " Nice value is out of range, Please use value between -19 and 20"
		exit 1
	fi

	echo -e "Starting ${@:3}"

	setsid $EXARUN loop "${@:2}" &

	if [ "$?" == "0" ]; then
		echo "OK"
	else
		echo "ERROR"
	fi
}

exa_run_status() {
	if [ "$2" == "" ]; then
		exa_run_usage
	fi

	if [ -e "/root/.exa-run/pids/${2}" ]; then
		PID=`cat /root/.exa-run/pids/${2}`
	else
		echo "Procces not started"
		exit 1
	fi

	if ps -p ${PID} > /dev/null 2>&1; then
		echo "OK"
		exit 0
i	else
		echo "Procces not running"
		exit 1
	fi
}

exa_run_renice() {
	NICEFILE="${HOME}/.exa-run/$2.nice"
	OLDNICE=`cat $NICEFILE`

	if [ -e "/root/.exa-run/pids/${2}" ]; then
		PID=`cat /root/.exa-run/pids/${2}`
		GPID=`ps opgid= "${PID}" | xargs`
	else
		echo "Procces not started"
		exit 1
	fi

	if [ ! -z "${GPID}" ]; then
		/usr/bin/renice ${3} -g ${GPID}
		echo "Reniced form ${OLDNICE} to $3 app $2"
	else
		echo "No procces found for appshort $2"
		exit 1
	fi

	echo "${1}" > ${NICEFILE}
}

exa_run_set_nice() {
	NICEFILE="${HOME}/.exa-run/$2.nice"

	if [ -z "${NICE}" ]; then
		if [ -f ${NICEFILE}  ]; then
			NICE=`cat ${NICEFILE}`
		else
			NICE=0
		fi
	fi

	echo "${NICE}"
}

exa_run_set_interpreter() {
	SCRIPTEXT="${1##*.}"

	if [ ${SCRIPTEXT} == 'js' ]; then
		echo '/usr/bin/node'
	elif [ ${SCRIPTEXT} == 'php' ]; then
		echo '/usr/bin/php'
    else
		echo "echo Unknown EXT ${SCRIPTEXT}"
	fi

}

exa_run_stop() {
	if [ "$2" == "" ]; then
		exa_run_usage
	fi

	if [ -f "${HOME}/.exa-run/pids/$2" ]; then
		echo -n "Stopping $2 "
		JOBPWD=`cat ${HOME}/.exa-run/$2.pwd`
		JOBCMD=`cat ${HOME}/.exa-run/$2.cmd`
		JOBPID=`cat ${HOME}/.exa-run/pids/$2`
		GPID=`ps opgid= "${JOBPID}" | xargs`
		if [ "X${GPID}X" == "XX" ]; then
			exa_error "Group PID of job $2 not found"
			exa_run_stop_cleanup "$@"
			exit 102
		else
			FOUND=`ps -o pid= -${GPID} | wc -l`
			if [ "X${FOUND}X" != "X0X" ]; then
				kill -TERM -${GPID}
				FOUND=`ps -o pid= -${GPID} | wc -l`
				for waiting in 1 2 3 4 5; do
					if [ "X${FOUND}X" == "X0X" ]; then
						exa_run_stop_cleanup "$@"
						echo " Stopped"
						return
					fi
					sleep 1
					echo .
					FOUND=`ps -o pid= -${GPID} | wc -l`
				done
				kill -9 -${GPID}
				echo " Killed"
			else
			exa_error "Job already stopped"
			fi
			exa_run_stop_cleanup "$@"
		fi
	else
		exa_error "Could not find job $2"
		exit 102
	fi
}

exa_run_stop_cleanup() {
	if [ -f "${HOME}/.exa-run/pids/$2" ]; then
		rm ${HOME}/.exa-run/pids/$2
	fi

	if [ -f "${HOME}/.exa-run/$2.pwd" ]; then
		rm ${HOME}/.exa-run/$2.pwd
	fi

	if [ -f "${HOME}/.exa-run/$2.cmd" ]; then
		rm ${HOME}/.exa-run/$2.cmd
	fi

	if [ -f "${HOME}/.exa-run/$2.nice" ]; then
		rm ${HOME}/.exa-run/$2.nice
	fi
}

exa_run_log() {
	if [ -f "${HOME}/.exa-run/${2}.log" ]; then
		cat ${HOME}/.exa-run/${2}.log
	fi
}

exa_run_tail() {
	if [ -f "${HOME}/.exa-run/${2}.log" ]; then
		tail -f ${HOME}/.exa-run/${2}.log
	fi 
}

exa_run_list() {
	for JOB in ${HOME}/.exa-run/pids/*; do
		if [ -f "${JOB}" ]; then
			JOBPID=`cat ${JOB}`
			JOBNAME=`basename ${JOB}`
			GPID=`ps opgid= "${JOBPID}" | xargs`
			if [ "X${GPID}X" == "XX" ]; then
				echo "*** ${JOBNAME} ***"
				echo "Not found"
			else
				echo "*** ${JOBNAME} ***"
				ps -o pid=,stat=,args=,etime=,nice= -${GPID}
			fi
		fi
	done
}

exa_run_restart_all() {
	for JOB in ${HOME}/.exa-run/pids/*; do
		if [ -f "${JOB}" ]; then
			JOBPID=`cat ${JOB}`
			JOBNAME=`basename ${JOB}`
			GPID=`ps opgid= "${JOBPID}" | xargs`
			if [ "X${GPID}X" == "XX" ]; then
				echo "*** ${JOBNAME} ***"
				echo "Not found"
			else
				echo "*** ${JOBNAME} ***"
				exa_run_stop stop ${JOBNAME}
				cd ${JOBPWD}
				exa_run_start restart ${JOBNAME} ${JOBCMD}
			fi
		fi
	done

}

exa_run_loop() {
	if [ "$3" == "" ]; then
		exa_run_usage
	fi


	INTERPRETER=`exa_run_set_interpreter "$3"`
	NICELEVEL=`exa_run_set_nice $@`

	echo $$ > ${HOME}/.exa-run/pids/$2
	echo `pwd` > ${HOME}/.exa-run/$2.pwd
	echo "${@:3}" > ${HOME}/.exa-run/$2.cmd
	echo "${NICELEVEL}" > ${HOME}/.exa-run/$2.nice

	while (true); do
		RESOLVMD5BEFORE=`md5sum /etc/resolv.conf`
		echo "$(exa_run_timestamp) *** Starting ${INTERPRETER} ${@:3}" >> ${HOME}/.exa-run/$2.log
		/usr/bin/nice -n ${NICELEVEL} "${INTERPRETER}" "${@:3}" >> ${HOME}/.exa-run/$2.log 2>&1
		RV=$?
		echo "$(exa_run_timestamp) *** Stopped\(${RV}\) ${@:3}" >> ${HOME}/.exa-run/$2.log
		RESOLVMD5AFTER=`md5sum /etc/resolv.conf`
		REPORT="Y"
		if [ "${RV}" == "39" ]; then
			if [ "${RESOLVMD5BEFORE}" != "${RESOLVMD5AFTER}" ]; then
				REPORT="N"
			fi
		fi
		if [ "${REPORT}" == "Y" ]; then
			sleep 10
			echo "restarted $2" > "/var/ednl/errors/`date +%s`-Restarted $2"
		fi
	done
}

exa_run_cleanlogs() {
	if [ "$2" == "" ]; then
		exa_run_usage
	fi

	if [ ! -f "${HOME}/.exa-run/pids/$2" ]; then
		if [ -f "${HOME}/.exa-run/$2.log" ]; then
			rm ${HOME}/.exa-run/${2}.log
		fi
	else
		exa_error "Job ${2} is still running"
		exit 103
	fi
}

exa_run_usage() {
    exa_error "Usage: NICE=<level> `basename \"$0\"` (start|stop|status|renice|restart|restartall|cleanrestart|cleanlogs|list|help)"
    echo "exa-run.sh"
    echo "	start <appshort> <command>"
    echo "	Start app"
    echo
    echo "	stop <appshort>"
    echo "	Stop app"
    echo
    echo "	status <appshort>"
    echo "	shows status from given appshort"
    echo
    echo "	renice <appshort> <nice>"
    echo "	changes the nice level"
    echo
    echo "	restart <appshort>"
    echo "	Restart app"
    echo
    echo "	restartall"
    echo "	Restart all apps"
    echo
    echo "	cleanrestart <appshort>"
    echo "	Restarts app and cleans logs"
    echo
    echo "	cleanlogs <appshort>"
    echo "	Cleans logs from procces"
    echo
    echo "	list"
    echo "	Shows all running procceses"
    echo
    echo -e "	\e[1mNICE LEVEL\e[0m"
    echo "	Nice level can be set with predifined variable. Niceness values range from -20"
    echo "	(most favorable to the process)  to 19 (least favorable to the process). when"
    echo "	\$NICE is empty it will use the default value(0)."
    echo
    echo -e "		\e[4mEXAMPLE\e[0m: \`NICE=10 exa-run start xmpl /bin/example\`"

    exit 2                              # LSB: invalid or excess argument(s)
}

exa_run_timestamp() {
	echo "[`date '+%Y-%m-%d %H:%M:%S'`]"
}

exa_run_init
ACTION=$1
EXARUN="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

case "${ACTION}" in
  start)
    exa_run_start "$@"
    ;;
  stop)
    exa_run_stop "$@"
    ;;
  status)
    exa_run_status "$@"
    ;;
  renice)
    exa_run_renice "$@"
    ;;
  restart)
	exa_run_stop "$@"
	cd ${JOBPWD}
	exa_run_start restart $2 ${JOBCMD}
    ;;
  restartall)
	  exa_run_restart_all
    ;;
  cleanrestart)
	exa_run_stop "$@"
	exa_run_cleanlogs "$@"
	cd ${JOBPWD}
	exa_run_start restart $2 ${JOBCMD}
    ;;
  loop)
	exa_run_loop "$@"
	;;
  cleanlogs)
	exa_run_cleanlogs "$@"
	;;
  list)
    exa_run_list "$@"
    ;;
  log)
    exa_run_log "$@"
    ;;
  tail)
    exa_run_tail "$@"
    ;;
  help)
    exa_run_usage "$@"
    ;;
  *)
	exa_run_usage "$@"
    ;;
esac

