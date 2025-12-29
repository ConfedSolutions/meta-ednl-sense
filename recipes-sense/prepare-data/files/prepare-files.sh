#!/usr/bin/env sh

if [ ! -d /data/os/var ]; then
        tar -xvzf /default-data.tar.gz -C /data
        chown root:root /data -R

        if [[ ! -L /var ]]; then
                rm -rf /data/os/var
                mv /var /data/os/var

                rm -rf /data/os/var/lock /data/os/var/run
                ln -s /run/lock /data/os/var/lock
                ln -s /run /data/os/var/run

                mv /default-data.tar.gz /factory-data.tar.gz
                tar -czf /default-data.tar.gz /data/.
        fi
        rm -rf /var
        ln -s /data/os/var /var

        systemctl reboot
fi

if [ ! -d /security/openvpn ]; then
	tar -xvzf /default-security.tar.gz -C /security
        chown root:root /security -R
fi

if [[ ! -L /var ]]; then
        rm -rf /var
        ln -s /data/os/var /var
fi

if [[ ! -L /srv ]]; then
        rm -rf /srv
        ln -s /data/os/srv /srv
fi

if [[ ! -L /home ]]; then
        cp -r /home/* /data/os/home/
        rm -rf /home
        ln -s /data/os/home /home
fi

if [[ ! -L /home/app ]]; then
        rm -rf /home/app
        ln -s /data/ednl/apps /home/app
fi

if [[ ! -L /root ]]; then
        cp /root/* -r /data/users/root/
        rm -rf /root
        ln -s /data/users/root /root
fi

if [ ! -d /etc/ednl ]; then
        ln -s /data/ednl/etc /etc/ednl
fi

if [ ! -d /data/os/var/ednl ]; then
        ln -s /data/ednl/var /data/os/var/ednl
fi

if [ ! -d /data/os/home/app/sense ]; then
        ln -s /data/ednl/apps /data/os/home/app/sense
fi

if [[ ! -L /etc/openvpn ]]; then
        rm -rf /etc/openvpn
        ln -s /security/openvpn /etc/openvpn
fi

if [[ ! -L /usr/lib/python3.12/site-packages ]]; then
	mv /usr/lib/python3.12/site-packages /data/software/python3-packages
	ln -s /data/software/python3-packages /usr/lib/python3.12/site-packages
fi

if [[ ! -L /sbin/crontab ]]; then
	ln -s /usr/bin/crontab /sbin/crontab
fi

if [[ ! -L /sbin/unzip ]]; then
	ln -s /usr/bin/unzip /sbin/unzip
fi

if [[ ! -L /sbin/tar ]]; then
	ln -s /usr/bin/tar /sbin/tar
fi

echo "Done."
