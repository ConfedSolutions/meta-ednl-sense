SUMMARY = "EDNL Sense demo application to drive the WF24MTLAJDNT0#"
DESCRIPTION = "Provide the application draw-image to draw 320x240 images to the screen and create a startup service to show the EDNL logo"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = "file://LICENSE;md5=9a1d9cf99c61b1ab65cfc89cbbc38a0b"

inherit systemd

PV = "0.1"

SRC_URI = "git://github.com/ConfedSolutions/winstar-display-sense;protocol=https;branch=main \
	file://ednl_logo.png \
	file://logo-on-boot.service \
"

SRCREV = "d5a943c1d46009c3126817ba62e9da5ff1ef9539"

S = "${WORKDIR}/git"

SYSTEMD_SERVICE:${PN} = "logo-on-boot.service"
SYSTEMD_PACKAGES = "${PN}"

TARGET_CC_ARCH += "${LDFLAGS}"
EXTRA_OEMAKE = "'CC=${CC}' 'RANLIB=${RANLIB}' 'AR=${AR}' 'CLFLAGS=${CFLAGS} -I${S}/.' 'LDFLAGS=${LDFLAGS}' 'BUILDDIR=${S}'"

FILES:${PN} += "/ednl_logo.png"

do_install:append () {
	oe_runmake install DESTDIR=${D} BINDIR=${bindir} SBINDIR=${sbindir} MANDIR=${mandir} INCLUDEDIR=${includedir}
	install -m 644 ${WORKDIR}/ednl_logo.png ${D}/ednl_logo.png
	install -d ${D}${systemd_unitdir}/system
	install -m 0644 ${WORKDIR}/logo-on-boot.service ${D}${systemd_unitdir}/system/
}

DEPENDS = "libgpiod"
RDEPENDS:${PN} = " libgpiod"

