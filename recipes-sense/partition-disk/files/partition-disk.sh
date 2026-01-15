#!/usr/bin/env sh

DISK=$(cat /proc/cmdline  | sed -E 's|.+root=(/dev/mmcblk[0-9])p[0-9].+|\1|gi')
PARTITION=$(cat /proc/cmdline  | sed -E 's|.+root=(/dev/mmcblk[0-9]p[0-9]).+|\1|gi')
MESSAGE=""

if [ ! -e "${DISK}p3" ]; then
        echo "Data partition not found, creating proper partition table"
        MESSAGE="Data partition not found, creating proper partition table"

        (echo d; \
        echo n; echo p; echo 1; echo 16384;   echo 2113535; \
        echo n; echo p; echo 2; echo 2113536; echo 4210687; \
        echo n; echo p; echo 3; echo 4210688; echo 4415487; \
        echo n; echo p; echo 4; echo 4415488; echo ""; \
        echo p; echo w) | fdisk -u "${DISK}"

        # make sure we clear the existing partitions
        # dd if=/dev/zero of="${DISK}" bs=512 seek=2113536 count=10
        dd if=/dev/zero of="${DISK}" bs=512 seek=4210688 count=10
        dd if=/dev/zero of="${DISK}" bs=512 seek=4415488 count=10

        sync; sleep 1;

        systemctl reboot;
elif ! dumpe2fs "${DISK}p3" > /dev/null 2>&1; then
        echo "No valid data file system found, creating file systems"
        MESSAGE="No valid data file system found, creating file systems"

        mkdir -p /data /security

        # create the file systems
        tune2fs -L rootfs1 "${DISK}p1"
        resize2fs "${DISK}p1"
        mkfs.ext4 "${DISK}p2" -L rootfs2
        mkfs.ext4 "${DISK}p3" -L security
        mkfs.ext4 "${DISK}p4" -L data

        sync

        sed -i "s|tmpfs                /var/volatile|#tmpfs                /var/volatile|gi" /etc/fstab
        sed -i 's|^\(/dev/root[[:space:]]\+/[[:space:]]\+auto[[:space:]]\+\)defaults\([[:space:]]\+\)|\1defaults,ro\2|' /etc/fstab
        echo "${DISK}p3  /security   ext4    defaults        0       0" >> /etc/fstab
        echo "${DISK}p4  /data   ext4    defaults        0       0" >> /etc/fstab
        echo "overlay  /var       overlay  lowerdir=/var,upperdir=/data/overlay/var/upper,workdir=/data/overlay/var/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /etc/ednl  overlay  lowerdir=/etc/ednl,upperdir=/data/overlay/etc/ednl/upper,workdir=/data/overlay/etc/ednl/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /home      overlay  lowerdir=/home,upperdir=/data/overlay/home/upper,workdir=/data/overlay/home/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /srv       overlay  lowerdir=/srv,upperdir=/data/overlay/srv/upper,workdir=/data/overlay/srv/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /root      overlay  lowerdir=/root,upperdir=/data/overlay/root/upper,workdir=/data/overlay/root/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /root      overlay  lowerdir=/root,upperdir=/data/overlay/root/upper,workdir=/data/overlay/root/work,x-systemd.requires=data.mount,x-systemd.after=data.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab
        echo "overlay  /etc/openvpn  overlay  lowerdir=/etc/openvpn,upperdir=/security/overlay/etc/openvpn/upper,workdir=/security/overlay/etc/openvpn/work,x-systemd.requires=security.mount,x-systemd.after=security.mount,x-systemd.requires=prepare-files.service,x-systemd.after=prepare-files.service  0  0" >> /etc/fstab

        sync
        
        systemctl reboot;
else
        echo "Storage medium is partitioned as needed";
        MESSAGE="Storage medium is partitioned as needed";
fi

systemd-notify --status="$MESSAGE" --ready
echo "Done."
