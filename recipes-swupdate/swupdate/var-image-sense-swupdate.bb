# Copyright (C) 2017 Variscite Ltd
# Released under the MIT license (see COPYING.MIT for the terms)

DESCRIPTION = "Initial image with SWupdate support"
LICENSE = "MIT"

SWUPDATE_BASE_IMAGE ??= "recipes-sense/images/fsl-image-sense-prod.bb"
require ${SWUPDATE_BASE_IMAGE}

CORE_IMAGE_EXTRA_INSTALL += " \
	swupdate \
	swupdate-www \
	kernel-image \
	kernel-devicetree \
"

QBSP_IMAGE_CONTENT = ""

# Due to the SWUpdate image will not fit the default NAND size.
# Removing default ubi creation for this image
IMAGE_FSTYPES:remove = "multiubi"
