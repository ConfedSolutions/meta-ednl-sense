# Copyright (C) 2025 Confed Solutions
# Released under the MIT license (see COPYING.MIT for the terms)

DESCRIPTION = "Production image for EDNL Sense v4."
LICENSE = "MIT"

inherit extrausers

inherit core-image

IMAGE_FEATURES += " \
    ssh-server-openssh \
    hwcodecs \
"

CORE_IMAGE_EXTRA_INSTALL += " \
	packagegroup-sense-php \
	packagegroup-sense-python \
"

# Set the root password to 'admin' and add the 'confed' user with password 'confed'
# reference: https://developer.technexion.com/docs/embedded-software/linux/yocto/usage-guides/automatically-setting-a-root-password-in-yocto-recipes

EXTRA_USERS_PARAMS = "\
    usermod -p $(openssl passwd -6 admin | sed 's/\$/\\\$/g') root; \
    useradd -p $(openssl passwd -6 confed | sed 's/\$/\\\$/g') confed; \
"

systemd_disable_vt () {
    rm ${IMAGE_ROOTFS}${sysconfdir}/systemd/system/getty.target.wants/getty@tty*.service
}

IMAGE_PREPROCESS_COMMAND:append = " ${@ 'systemd_disable_vt;' if bb.utils.contains('DISTRO_FEATURES', 'systemd', True, False, d) and bb.utils.contains('USE_VT', '0', True, False, d) else ''} "

