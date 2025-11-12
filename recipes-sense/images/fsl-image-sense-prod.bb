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
	packagegroup-sense-update \
	packagegroup-sense-php \
	packagegroup-sense-python \
	packagegroup-sense-modem \
	packagegroup-variscite-swupdate \
"

# Set the root password to 'admin' and add the 'confed' user with password 'confed'
# reference: https://developer.technexion.com/docs/embedded-software/linux/yocto/usage-guides/automatically-setting-a-root-password-in-yocto-recipes

EXTRA_USERS_PARAMS = "\
    usermod -p $(openssl passwd -6 admin | sed 's/\$/\\\$/g') root; \
    useradd -p $(openssl passwd -6 confed | sed 's/\$/\\\$/g') confed; \
    usermod -a -G sudo confed \
"

systemd_disable_vt () {
    rm ${IMAGE_ROOTFS}${sysconfdir}/systemd/system/getty.target.wants/getty@tty*.service
}

IMAGE_PREPROCESS_COMMAND:append = " ${@ 'systemd_disable_vt;' if bb.utils.contains('DISTRO_FEATURES', 'systemd', True, False, d) and bb.utils.contains('USE_VT', '0', True, False, d) else ''} "

# give the sudo group root rights when using sudo
modify_sudoers() {
  sed -i 's/# %sudo/%sudo/g' ${IMAGE_ROOTFS}/etc/sudoers
}
ROOTFS_POSTPROCESS_COMMAND += "modify_sudoers;"

# configure the SSH server and add the authorized_keys
configure_sshd() {
  # configure the SSH server for security
  sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin prohibit-password/g' ${IMAGE_ROOTFS}/etc/ssh/sshd_config
  sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/g' ${IMAGE_ROOTFS}/etc/ssh/sshd_config

  # add public SSH keys to the authorized_keys file
  mkdir -p ${IMAGE_ROOTFS}/root/.ssh/
  echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIIBL34ZRj9VTiHB6yzli8oGq+Kay4shM2WOl0aleLMH5 Nick@5CD0237LSQ' > ${IMAGE_ROOTFS}/root/.ssh/authorized_keys
  chmod 700 ${IMAGE_ROOTFS}/root/.ssh/
  chmod 600 ${IMAGE_ROOTFS}/root/.ssh/authorized_keys
}
ROOTFS_POSTPROCESS_COMMAND += "configure_sshd;"

