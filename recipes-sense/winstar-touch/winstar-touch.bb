SUMMARY = "EDNL Sense demo application to readout the touch controller on the WF24MTLAJDNG0#"
DESCRIPTION = "Provide the application touch-test to readout the CF1124 chip of the display"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = "file://LICENSE;md5=9a1d9cf99c61b1ab65cfc89cbbc38a0b"

PV = "0.1"

SRC_URI = "git://github.com/ConfedSolutions/winstar-touch-sense;protocol=https;branch=main \
"

SRCREV = "410329a2eb13ab72830079523d8a2913dec72b7f"

S = "${WORKDIR}/git"

TARGET_CC_ARCH += "${LDFLAGS}"
EXTRA_OEMAKE = "'CC=${CC}' 'RANLIB=${RANLIB}' 'AR=${AR}' 'CLFLAGS=${CFLAGS} -I${S}/.' 'LDFLAGS=${LDFLAGS}' 'BUILDDIR=${S}'"

do_install:append () {
	oe_runmake install DESTDIR=${D} BINDIR=${bindir} SBINDIR=${sbindir} MANDIR=${mandir} INCLUDEDIR=${includedir}
}

DEPENDS = "libgpiod"
RDEPENDS:${PN} = " libgpiod"

