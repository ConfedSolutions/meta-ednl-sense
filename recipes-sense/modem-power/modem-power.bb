SUMMARY = "EDNL Sense modem power management"
DESCRIPTION = "Two scripts for managing the modems power state and initial configuration"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

PV = "1.0"

SRC_URI += "\
	file://LICENSE \
	file://modem_poweroff.sh \
	file://modem_poweron.sh \
"

do_install:append () {
	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/modem_poweroff.sh ${D}/usr/sbin/modem_off
	install -m 755 ${WORKDIR}/modem_poweron.sh ${D}/usr/sbin/modem_on
}

RDEPENDS:${PN} = " python3 python3-pyserial"

