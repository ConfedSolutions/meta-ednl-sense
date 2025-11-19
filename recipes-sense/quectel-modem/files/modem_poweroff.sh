#!/usr/bin/env sh

# check if the modem is powered on by checking if it's USB serial interface available
if [ -e /dev/ttymdmAT1 ]; then
	echo "Modem powered on"
	
	echo "Power off modem via PWRKEY pin"
	gpioset -z -c 0 22=1 # TODO use proper gpio port and pin
	sleep 0.5
	killall gpioset
	gpioset -z -c 0 22=0 # TODO use proper gpio port and pin
	killall gpioset
	
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

