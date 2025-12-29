SUMMARY = "EDNL Sense disk configuration tool"
DESCRIPTION = "During boot check if the partition layout is correct for the SWupdate tool to function properly"
AUTHOR = "nick.vanijzendoorn@confed.eu"
LICENSE = "MIT"
LIC_FILES_CHKSUM = " \
    file://${WORKDIR}/LICENSE;md5=d94bb7ec45aa701391e52d25d397c275 \
"

inherit systemd

PV = "1.5"

SRC_URI += "\
	file://LICENSE \
	file://partition-disk.sh \
	file://partition-disk.service \
"

SYSTEMD_SERVICE:${PN} = "partition-disk.service"
SYSTEMD_PACKAGES = "${PN}"

do_install:append () {
	install -d ${D}/usr/sbin/
	install -m 755 ${WORKDIR}/partition-disk.sh ${D}/usr/sbin/partition-disk
	install -d ${D}${systemd_unitdir}/system
	install -m 0644 ${WORKDIR}/partition-disk.service ${D}${systemd_unitdir}/system/
}

RDEPENDS:${PN} = " e2fsprogs-dumpe2fs e2fsprogs-mke2fs e2fsprogs-e2fsck e2fsprogs-resize2fs e2fsprogs-tune2fs"
