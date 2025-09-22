# Copyright (C) 2015 Freescale Semiconductor
# Released under the MIT license (see COPYING.MIT for the terms)

DESCRIPTION = "Freescale Image to validate i.MX machines. \
This image contains everything used to test i.MX machines including GUI, \
demos and lots of applications. This creates a very large image, not \
suitable for production."
LICENSE = "MIT"

inherit core-image

### WARNING: This image is NOT suitable for production use and is intended
###          to provide a way for users to reproduce the image used during
###          the validation process of i.MX BSP releases.

IMAGE_FEATURES += " \
    ssh-server-openssh \
    hwcodecs \
    debug-tweaks \
    nfs-client \
    tools-debug \
    tools-testapps \
"

CORE_IMAGE_EXTRA_INSTALL += " \
	packagegroup-core-full-cmdline \
	packagegroup-variscite-imx-security \
	packagegroup-sense-devel \
	packagegroup-sense-php \
	packagegroup-sense-python \
	packagegroup-sense-network \
"

CORE_IMAGE_EXTRA_INSTALL:append:mx9-nxp-bsp = "\
    packagegroup-variscite-swupdate \
"

systemd_disable_vt () {
    rm ${IMAGE_ROOTFS}${sysconfdir}/systemd/system/getty.target.wants/getty@tty*.service
}

IMAGE_PREPROCESS_COMMAND:append = " ${@ 'systemd_disable_vt;' if bb.utils.contains('DISTRO_FEATURES', 'systemd', True, False, d) and bb.utils.contains('USE_VT', '0', True, False, d) else ''} "
