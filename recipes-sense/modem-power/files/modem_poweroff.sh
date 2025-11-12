#!/usr/bin/env sh

# check if the modem is powered on by checking if it's USB serial interface available
if [ -e /dev/ttyUSB2 ]; then
	echo "Modem powered on"
	
	echo "- TODO - Power off modem via GPIO pin"
	
	# wait for the modem to power off
	while [ -e /dev/ttyUSB2 ]; do
		echo -n .
		sleep 1
	done
	
	echo ""
	echo "Modem has powered off"
else
	echo "Modem is not powered on"
fi

