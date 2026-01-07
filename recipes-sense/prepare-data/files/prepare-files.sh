#!/usr/bin/env sh

DATA_HASH="${md5sum /default-data.tar.gz | cut -d ' ' -f 1}"
SECURITY_HASH="${md5sum /default-security.tar.gz | cut -d ' ' -f 1}"

if [ ! -f "/data/.data-hash" ]; then
        tar -xvzf "/default-data.tar.gz" -C "/data"
        chown root:root "/data" -R
        echo "${DATA_HASH}" > "/data/.data-hash"      
fi

if [ ! -f "/security/.security-hash" ]; then
        tar -xvzf "/default-security.tar.gz" -C "/security"
        chown root:root "/security" -R
        echo "${SECURITY_HASH}" > "/security/.security-hash"      
fi

echo "Done."
