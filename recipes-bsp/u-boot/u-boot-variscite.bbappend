# Copyright (C) 2013-2016 Freescale Semiconductor
# Copyright 2017-2018 NXP
# Copyright 2018-2023 Variscite Ltd.
# Copyright 2025 Confed Solutions

FILESEXTRAPATHS:prepend := "${THISDIR}/${BPN}:"
FILESEXTRAPATHS:prepend := "${THISDIR}/u-boot-fw-utils:"

SRC_URI += " \
        file://0001-add-sense-support.patch  \
"
