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
    file://logo-on-boot.service \
	file://default \
"

SYSTEMD_SERVICE:${PN} = "prepare-files.service logo-on-boot.service"
SYSTEMD_PACKAGES = "${PN}"

do_install:append () {
	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/prepare-files.sh ${D}/usr/sbin/prepare-files
	install -d ${D}${systemd_unitdir}/system
	install -m 0644 ${WORKDIR}/prepare-files.service ${D}${systemd_unitdir}/system/
    install -m 0644 ${WORKDIR}/logo-on-boot.service ${D}${systemd_unitdir}/system/
	
    if [ -d "${WORKDIR}/default" ]; then
        cd ${WORKDIR}/default

        find . -type d -exec install -d ${D}/{} \;
        find . -type f -exec install -m 0644 {} ${D}/{} \;
    fi
}

FILES:${PN} += " \
    ${sysconfdir}/ednl \
    /var/ednl/ \
    /home/app \
"
