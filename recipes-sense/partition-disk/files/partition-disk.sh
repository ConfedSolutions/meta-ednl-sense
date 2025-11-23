#!/usr/bin/env sh

DISK=`cat /proc/cmdline  | sed -E 's|.+root=(/dev/mmcblk[0-9])p[0-9].+|\1|gi'`
PARTITION=`cat /proc/cmdline  | sed -E 's|.+root=(/dev/mmcblk[0-9]p[0-9]).+|\1|gi'`
MESSAGE=""

if [ ! -e ${DISK}p3 ]; then
	echo "Data partition not found, creating proper partition table"
	MESSAGE="Data partition not found, creating proper partition table"
	
	(echo d; \
	 echo n; echo p; echo 1; echo 16384; echo 4210687; \
	 echo n; echo p; echo 2; echo 4210688; echo 8404991; \
	 echo n; echo p; echo 3; echo 8404992; echo ""; \
	 echo p; echo w) | fdisk -u ${DISK}
	
	sync; sleep 1;
	
	systemctl reboot;
elif ! dumpe2fs ${DISK}p3 > /dev/null 2>&1; then
	echo "No valid data file system found, creating file systems"
	MESSAGE="No valid data file system found, creating file systems"
	
	# create the file systems
	tune2fs -L rootfs1 ${DISK}p1
	resize2fs ${DISK}p1
	mkfs.ext4 ${DISK}p2 -L rootfs2
	mkfs.ext4 ${DISK}p3 -L data
	
	sync; sleep 1;
else
	echo "Storage medium is partitioned as needed";
	MESSAGE="Storage medium is partitioned as needed";
fi

systemd-notify --status=$MESSAGE --ready
echo "Done."

