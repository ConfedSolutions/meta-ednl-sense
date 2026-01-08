SUMMARY = "EDNL custom repo for RPM packages"
DESCRIPTION = "Puts a custom repo file in yum.repos.d"
AUTHOR = "dgr@ednl.nl"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

PV = "1.5"

SRC_URI += "\
	file://LICENSE \
	file://ednl-sense-rpm.repo \
"

do_install:append () {
	install -d ${D}/etc/yum.repos.d
	install -m 755 ${WORKDIR}/ednl-sense-rpm.repo ${D}/etc/yum.repos.d/ednl-sense-rpm.repo
}