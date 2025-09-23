# ednl-sensev4-yocto-meta
Yocto layer for the EDNL Sense v4

To use this layer use the following steps

# install repo
```
mkdir ~/bin
curl http://commondatastorage.googleapis.com/git-repo-downloads/repo  > ~/bin/repo
chmod a+x ~/bin/repo
PATH=${PATH}:~/bin
```

# create imx-yocto-bsp
```
mkdir var-fsl-yocto
cd var-fsl-yocto
repo init -u https://github.com/varigit/variscite-bsp-platform.git -b scarthgap -m imx-6.6.52-2.2.0.xml
repo sync
```

# integrate meta-ednl-sense recipes into the Yocto code base:
```
cd ./sources
git clone https://github.com/EDNL-BV/ednl-sensev4-yocto-meta.git meta-ednl-sense
cd ..
```

# initialize the Yocto build environment
```
#MACHINE=imx91-var-som-sense DISTRO=fsl-imx-xwayland source var-setup-release.sh -b build_sense_v4
MACHINE=imx91-var-som-sense DISTRO=fsl-imx-fb source var-setup-release.sh -b build_sense_v4
```

# add the EDNL Sense Yocto layer to the build environment
```
echo 'BBLAYERS += "${BSPDIR}/sources/meta-ednl-sense"' >> conf/bblayers.conf
```

# build the base image
```
bitbake fsl-image-sense
```

