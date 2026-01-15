SUMMARY = "EDNL Sense Package Groups"

PACKAGE_ARCH = "${MACHINE_ARCH}"

inherit packagegroup

PACKAGES = "\
    ${PN}-base \
    ${PN}-devel \
    ${PN}-display \
    ${PN}-update \
    ${PN}-php \
    ${PN}-python \
    ${PN}-modem \
    ${PN}-network \
"

RDEPENDS:${PN}-base = "\
    cronie \
    curl \
    nano \
    opkg \
    libgpiod \
    libgpiod-tools \
    libgpiodcxx \
    kmod \
    var-mii \
    vim \
    unzip \
    which \
    zstd \
"

RDEPENDS:${PN}-devel = "\
    can-utils \
    curl \
    ldd \
    unzip \
    which \
    devmem2 \
    expect \
    gptfdisk \
    hostapd \
    hdparm \
    i2c-tools \
    iperf3 \
    kmod \
    libgpiod \
    libgpiod-dev \
    libgpiod-tools \
    libgpiodcxx \
    minicom \
    mmc-utils \
    nano \
    rng-tools \
    spidev-test \
    screen \
    var-mii \
    zstd \
    xz \
"

RDEPENDS:${PN}-display = "\
    winstar-display \
"

#    prepare-data
#    swupdate

RDEPENDS:${PN}-update = "\
    partition-disk \
    prepare-data \
    custom-repo \
    kernel-image \
    kernel-devicetree \
"

RDEPENDS:${PN}-php = "\
    php \
    php-cli \
"

RDEPENDS:${PN}-python = "\
    python3 \
    python3-modules \
    python3-pyserial \
    python3-gpiod \
    python3-pip \
"

RDEPENDS:${PN}-modem = "\
    quectel-modem \
"

RDEPENDS:${PN}-network = "\
    bridge-utils \
    conntrack-tools \
    ethtool \
    dnsmasq \
    net-tools \
    ntp \
    openssh-sftp-server \
    openvpn \
    tcpdump \
    socat \
    netcat \
    iptables \
    iputils \
    wget \
"
