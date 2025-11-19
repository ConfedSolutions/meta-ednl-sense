# Copyright (C) 2025 Confed Solutions
# Released under the MIT license (see COPYING.MIT for the terms)

DESCRIPTION = "Development image for the sense with extended debug support. And empty root password."
LICENSE = "MIT"

inherit core-image

### WARNING: This image is NOT suitable for production since there is no root password set

IMAGE_FEATURES += " \
    ssh-server-openssh \
    hwcodecs \
    debug-tweaks \
    nfs-client \
"

CORE_IMAGE_EXTRA_INSTALL += " \
	packagegroup-core-buildessential \
	packagegroup-core-full-cmdline \
	packagegroup-sense-devel \
	packagegroup-sense-php \
	packagegroup-sense-python \
	packagegroup-sense-modem \
	packagegroup-sense-network \
	packagegroup-variscite-swupdate \
"

systemd_disable_vt () {
    rm ${IMAGE_ROOTFS}${sysconfdir}/systemd/system/getty.target.wants/getty@tty*.service
}

IMAGE_PREPROCESS_COMMAND:append = " ${@ 'systemd_disable_vt;' if bb.utils.contains('DISTRO_FEATURES', 'systemd', True, False, d) and bb.utils.contains('USE_VT', '0', True, False, d) else ''} "
