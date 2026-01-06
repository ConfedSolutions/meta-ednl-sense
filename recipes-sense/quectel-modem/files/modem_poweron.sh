#!/usr/bin/env sh

# check if the modem is powered on
if [ ! -e /dev/ttymdmAT1 ]; then
	echo "Modem powered off"
	
	echo "Power on modem via PWRKEY pin"
	gpioset -p 500ms -t 0 -c 0 22=1 # TODO use proper gpio port and pin
	gpioset -p 500ms -t 0 -c 0 22=0 # TODO use proper gpio port and pin
	
	# wait for the modem to have booted
	while [ ! -e /dev/ttymdmAT1 ]; do
		echo -n .
		sleep 1
	done
	
	# avoid a race condition where the USB serial is there but the network interface not yet
	echo ""
	echo "Modem firmware booted"
	sleep 1
else
	echo "Modem already powered on"
fi

# check if the modem is configured for cdc_ether mode
echo -n "Check if modem in cdc_ether mode: "
if [ ! -e /sys/class/net/mobile ]; then
	echo "no, switching"
	sleep 2
	modem-send "AT+QCFG=\"usbnet\",1"
else
	echo "yes, ready"
fi

echo "Modem ready for usage"

