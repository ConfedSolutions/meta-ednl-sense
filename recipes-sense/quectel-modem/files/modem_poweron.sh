#!/usr/bin/env sh

# check if the modem is powered on
if [ ! -e /dev/ttymdmAT1 ]; then
	echo "Modem powered off"
	
	# toggle the line 600ms high (min 500ms), 100ms low and then release the GPIO leaving it low
	echo "Power on modem via PWRKEY pin"
	gpioset -t 600ms,100ms,0 -C modem_poweron -c 1 29=1
	
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

# disable command echo for modem-send to work correctly
while ! modem-send -v "ATE0"; do
	echo "retrying to disable command echoing";
done

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

