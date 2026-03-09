#!/usr/bin/env sh

# disable command echo for modem-send to work correctly
while ! modem-send -v "ATE0"; do
	echo "retrying to disable command echoing";
done

# normalize IPv6 address printing
modem-send "AT+CGPIAF=1,1,1,0"

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

