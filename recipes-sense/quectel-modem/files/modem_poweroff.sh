#!/usr/bin/env sh

# check if the modem is powered on by checking if it's USB serial interface available
if [ -e /dev/ttymdmAT1 ]; then
	echo "Modem powered on"
	
	echo "Power off modem via PWRKEY pin"
	gpioset -p 500ms -t 0 -c 1 29=1
	gpioset -p 500ms -t 0 -c 1 29=0
	
	# wait for the modem to power off
	while [ -e /dev/ttymdmAT1 ]; do
		echo -n .
		sleep 1
	done
	
	echo ""
	echo "Modem has powered off"
else
	echo "Modem is not powered on"
fi

