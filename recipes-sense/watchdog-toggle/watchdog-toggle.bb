SUMMARY = "Startup script to toggle the watchdog"
DESCRIPTION = "Startup script to toggle the watchdog"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

inherit systemd

PV = "1.5"

SRC_URI += "\
	file://LICENSE \
	file://watchdog-toggle.service \
"

SYSTEMD_SERVICE:${PN} = "watchdog-toggle.service"
SYSTEMD_PACKAGES = "${PN}"

do_install:append () {
	install -d ${D}${systemd_unitdir}/system
	install -m 0644 ${WORKDIR}/watchdog-toggle.service ${D}${systemd_unitdir}/system/
}

RDEPENDS:${PN} = " libgpiod-tools"
