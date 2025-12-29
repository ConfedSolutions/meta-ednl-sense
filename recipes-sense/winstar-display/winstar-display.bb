SUMMARY = "EDNL Sense demo application to drive the WF24MTLAJDNT0#"
DESCRIPTION = "Provide the application draw-image to draw 320x240 images to the screen"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = "file://LICENSE;md5=9a1d9cf99c61b1ab65cfc89cbbc38a0b"

PV = "0.1"

SRC_URI = "git://github.com/ConfedSolutions/winstar-display-sense;protocol=https;branch=main \
	file://ednl_logo.png \
"

SRCREV = "c7d9a263068785bc4eccfd0aa54ab656f599d8dc"

S = "${WORKDIR}/git"

TARGET_CC_ARCH += "${LDFLAGS}"
EXTRA_OEMAKE = "'CC=${CC}' 'RANLIB=${RANLIB}' 'AR=${AR}' 'CLFLAGS=${CFLAGS} -I${S}/.' 'LDFLAGS=${LDFLAGS}' 'BUILDDIR=${S}'"

FILES:${PN} += "/root/*"

do_install:append () {
	oe_runmake install DESTDIR=${D} BINDIR=${bindir} SBINDIR=${sbindir} MANDIR=${mandir} INCLUDEDIR=${includedir}

	install -d ${D}/root
	install -m 644 ${WORKDIR}/ednl_logo.png ${D}/root/ednl_logo.png
}

DEPENDS = "libgpiod"
RDEPENDS:${PN} = " libgpiod"

