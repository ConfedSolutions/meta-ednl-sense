#!/usr/bin/env sh

# check if the modem is powered on
if [ ! -e /dev/ttyUSB2 ]; then
	echo "Modem powered off"
	
	echo "- TODO - Power on modem via GPIO pin"
	
	# wait for the modem to have booted
	while [ ! -e /dev/ttyUSB2 ]; do
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
if [ ! -e /sys/class/net/usb0 ]; then
	echo "no, switching"
	sleep 2
	echo "import serial; serial.Serial('/dev/ttyUSB2', 115200)" | python3
	echo -ne "AT+QCFG=\"usbnet\",1\r" > /dev/ttyUSB2
else
	echo "yes, ready"
fi

echo "Modem ready for usage"

