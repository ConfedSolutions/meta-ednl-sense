SUMMARY = "EDNL binary files for essential commands"
DESCRIPTION = "Puts a set of binaries in /usr/sbin or /root/bin that are essential for the Sense."
AUTHOR = "dgr@ednl.nl"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

PV = "1.5"

SRC_URI += "\
	file://LICENSE \
	file://exa-run \
"

do_install:append () {
	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/exa-run ${D}/usr/sbin/exa-run
}