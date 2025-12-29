SUMMARY = "EDNL default files for /data and /security partitions"
DESCRIPTION = "During boot, after mounting /data and /security, check if the data and security partitions have all default files/folders"
AUTHOR = "dgr@ednl.nl"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

inherit systemd

PV = "1.5"

SRC_URI += "\
	file://LICENSE \
	file://prepare-files.sh \
	file://prepare-files.service \
	file://default-data.tar.gz;unpack=false \
	file://default-security.tar.gz;unpack=false \
"

SYSTEMD_SERVICE:${PN} = "prepare-files.service"
SYSTEMD_PACKAGES = "${PN}"

do_install:append () {
	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/prepare-files.sh ${D}/usr/sbin/prepare-files
	install -d ${D}${systemd_unitdir}/system
	install -m 0644 ${WORKDIR}/prepare-files.service ${D}${systemd_unitdir}/system/
	install -m 0644 ${WORKDIR}/default-data.tar.gz ${D}/default-data.tar.gz
	install -m 0644 ${WORKDIR}/default-security.tar.gz ${D}/default-security.tar.gz
}

FILES:${PN} += "/default-data.tar.gz"
FILES:${PN} += "/default-security.tar.gz"
