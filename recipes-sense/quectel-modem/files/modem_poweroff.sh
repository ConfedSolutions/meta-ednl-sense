#!/usr/bin/env sh

# check if the modem is powered on by checking if it's USB serial interface available
if [ -e /dev/ttymdmAT1 ]; then
	echo "Modem powered on"
	
	# toggle the line 750ms high (min 650ms), 100ms low and then release the GPIO leaving it low
	echo "Power off modem via PWRKEY pin"
	gpioset -t 750ms,100ms,0 -C modem_poweroff -c 1 29=1
	
	# wait for the modem to power off, this can take more then 30 seconds according to the datasheet
	while [ -e /dev/ttymdmAT1 ]; do
		echo -n .
		sleep 1
	done
	
	echo ""
	echo "Modem has powered off"
else
	echo "Modem is not powered on"
fi

