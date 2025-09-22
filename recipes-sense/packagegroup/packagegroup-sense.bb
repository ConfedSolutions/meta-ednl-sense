SUMMARY = "EDNL Sense Package Group"

PACKAGE_ARCH = "${MACHINE_ARCH}"

inherit packagegroup

PACKAGES = "\
    ${PN}-devel \
    ${PN}-python \
    ${PN}-php \
    ${PN}-network \
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
"

RDEPENDS:${PN}-python = "\
    python3 \
    python3-modules \
    python3-pyserial \
    python3-pip \
"

RDEPENDS:${PN}-php = "\
    php \
    php-cli \
"

RDEPENDS:${PN}-network = "\
    bridge-utils \
    conntrack-tools \
    ethtool \
    dnsmasq \
    net-tools \
    ntp \
    lighttpd \
    openssh-sftp-server \
    openvpn \
    tcpdump \
    socat \
    netcat \
    iptables \
    iputils \
    wget \
"
