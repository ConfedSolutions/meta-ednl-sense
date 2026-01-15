#!/usr/bin/env sh
set -eu

mkdir -p /data/overlay/var/upper /data/overlay/var/work
mkdir -p /data/overlay/etc/ednl/upper /data/overlay/etc/ednl/work
mkdir -p /data/overlay/home/upper /data/overlay/home/work
mkdir -p /data/overlay/srv/upper /data/overlay/srv/work
mkdir -p /data/overlay/root/upper /data/overlay/root/work
mkdir -p /security/overlay/etc/openvpn/upper /security/overlay/etc/openvpn/work

echo "Done."
