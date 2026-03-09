#!/usr/bin/env sh

# check if the modem is powered on
if [ ! -e /dev/ttymdmAT1 ]; then
	echo "Modem powered off"
	
	# toggle the line 600ms high (min 500ms) then low and release the GPIO leaving it low
	echo "Power on modem via the PWRKEY pin"
	gpioset -t 600ms,0 -C modem_poweron -c 1 29=1
	
	# wait for the modem to have booted, this can take more then 13 seconds according to the datasheet
	while [ ! -e /dev/ttymdmAT1 ]; do
		echo -n .
		sleep 1
	done
	
	# avoid a race condition where the USB serial is there but the network interface not yet
	echo ""
	echo "Modem firmware booted"
	sleep 2
else
	echo "Modem already powered on"
fi

# call the modem initialization script
modem_initialize

