#!/usr/bin/env sh

echo "!!! WARNING !!! only use if the modem doesn't respond"

# toggle the line 300ms high (between 150ms and 460ms) then release the GPIO leaving it low
echo "Reset the modem via the RESET pin"
gpioset -t 300ms,0 -C modem_hardreset -c 1 31=1

# wait for the modem to have started the reset sequence
sleep 2

# wait for the modem to have (re-)booted, this can take more then 13 seconds according to the datasheet
while [ ! -e /dev/ttymdmAT1 ]; do
	echo -n .
	sleep 1
done

# avoid a race condition where the USB serial is there but the network interface not yet
echo ""
echo "Modem firmware booted"
sleep 2

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

