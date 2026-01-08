#!/usr/bin/env sh
set -eu

DATA_HASH="$(md5sum /default-data.tar.gz | cut -d ' ' -f 1)"
SECURITY_HASH="$(md5sum /default-security.tar.gz | cut -d ' ' -f 1)"
REBOOT=false

if [ ! -f "/data/.data-hash" ]; then
  rm -rf /data/*
  tar -xvzf "/default-data.tar.gz" -C "/data"
  chown -R root:root "/data"
  echo "$DATA_HASH" > /data/.data-hash
  REBOOT=true
fi

if [ ! -f "/security/.security-hash" ]; then
  rm -rf /security/*
  tar -xvzf "/default-security.tar.gz" -C "/security"
  chown -R root:root "/security"
  echo "$SECURITY_HASH" > /security/.security-hash
  REBOOT=true
fi

if [ $REBOOT ]; then
  sleep 1
  systemctl reboot  
fi

echo "Done."
