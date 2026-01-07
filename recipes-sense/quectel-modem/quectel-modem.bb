SUMMARY = "EDNL Sense modem power management and interface configuration"
DESCRIPTION = "Two scripts for managing the modems power state and initial configuration, Udev rules and AT interface application"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = "file://LICENSE;md5=9a1d9cf99c61b1ab65cfc89cbbc38a0b"

PV = "1.1"

SRC_URI += "git://github.com/ConfedSolutions/modem-send;protocol=https;branch=main \
	file://modem_poweroff.sh \
	file://modem_poweron.sh \
	file://99-quectel-modems.rules \
"
SRCREV = "82e258c1771dc3d56263df8d9b062ebc1f0ab6d7"

S = "${WORKDIR}/git"

TARGET_CC_ARCH += "${LDFLAGS}"
EXTRA_OEMAKE = "'CC=${CC}' 'RANLIB=${RANLIB}' 'AR=${AR}' 'CLFLAGS=${CFLAGS} -I${S}/.' 'LDFLAGS=${LDFLAGS}' 'BUILDDIR=${S}'"

do_install:append () {
	oe_runmake install DESTDIR=${D} BINDIR=${bindir} SBINDIR=${sbindir} MANDIR=${mandir} INCLUDEDIR=${includedir}

	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/modem_poweroff.sh ${D}/usr/sbin/modem_off
	install -m 755 ${WORKDIR}/modem_poweron.sh ${D}/usr/sbin/modem_on
	
	install -d ${D}/etc/udev/rules.d/
	install -m 755 ${WORKDIR}/99-quectel-modems.rules ${D}/etc/udev/rules.d/
}

RDEPENDS:${PN} = " udev"

