SUMMARY = "Quectel modem update utility"
DESCRIPTION = "Quectel modem update utility"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = "file://${WORKDIR}/LICENSE;md5=9a1d9cf99c61b1ab65cfc89cbbc38a0b"

PV = "2.1"

SRC_URI += "file://qfirehose.tar.gz \
	file://LICENSE \
"

S = "${WORKDIR}"

TARGET_CC_ARCH += "${LDFLAGS}"
EXTRA_OEMAKE = "'CC=${CC}' 'RANLIB=${RANLIB}' 'AR=${AR}' 'CLFLAGS=${CFLAGS} -I${S}/.' 'LDFLAGS=${LDFLAGS}' 'BUILDDIR=${S}'"

do_install:append () {
	oe_runmake install DESTDIR=${D} BINDIR=${bindir} SBINDIR=${sbindir} MANDIR=${mandir} INCLUDEDIR=${includedir}
}

