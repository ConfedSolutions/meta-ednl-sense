SUMMARY = "EDNL Sense Package Group"

PACKAGE_ARCH = "${MACHINE_ARCH}"

inherit packagegroup

PACKAGES = "\
    ${PN}-base \
    ${PN}-devel \
    ${PN}-display \
    ${PN}-update \
    ${PN}-python \
    ${PN}-php \
    ${PN}-modem \
    ${PN}-network \
"

RDEPENDS:${PN}-base = "\
    cronie \
    nano \
    vim \
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

RDEPENDS:${PN}-update = "\
    partition-disk \
    custom-repo \
    prepare-data \
    swupdate \
    swupdate-www \
    kernel-image \
    kernel-devicetree \
"

RDEPENDS:${PN}-python = "\
    python3 \
    python3-modules \
    python3-pyserial \
    python3-gpiod \
    python3-pip \
"

RDEPENDS:${PN}-php = "\
    php \
    php-cli \
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
