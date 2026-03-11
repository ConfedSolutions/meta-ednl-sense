#!/usr/bin/env sh

# disable command echo for modem-send to work correctly
while ! modem-send -v "ATE0"; do
	echo "retrying to disable command echoing";
done

# disable the second antenna input
modem-send "AT+QCFG=\"divctl\",0"

# normalize IPv6 address printing
modem-send "AT+CGPIAF=1,1,1,0"

# display the identifiers
echo -n "[+] Module information"
modem-send "ATI"

echo -n "[+] ICCID:"
modem-send "AT+QCCID"

echo -n "[+] IMSI:"
modem-send "AT+CIMI"

echo -n "[+] IMEI:"
modem-send "AT+GSN"

echo -n "[+] Firmware Version:"
modem-send "AT+QGMR"

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

