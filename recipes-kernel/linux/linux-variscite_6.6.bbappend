FILESEXTRAPATHS:prepend := "${THISDIR}/${BPN}:"

SRC_URI += " \
        file://0001-add-sense-support.patch  \
        file://display-panels.cfg \
        file://overlay.cfg \
"
