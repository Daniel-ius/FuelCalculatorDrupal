
#ddev-generated - Do not modify this file; your modifications will be overwritten.
ARG BASE_IMAGE="scratch"


### From user Dockerfile /home/danielius/FuelCalculatorDrupal/.ddev/web-build/prepend.Dockerfile.frankenphp:
#ddev-generated
ARG DDEV_PHP_VERSION
ARG FRANKENPHP_DEBIAN_CODENAME=bookworm
FROM dunglas/frankenphp:php${DDEV_PHP_VERSION}-${FRANKENPHP_DEBIAN_CODENAME} AS frankenphp

### DDEV-injected base Dockerfile contents
FROM $BASE_IMAGE
SHELL ["/bin/bash", "-c"]

ARG TARGETPLATFORM
ARG TARGETARCH
ARG TARGETOS
ARG username
ARG uid
ARG gid
ARG DDEV_PHP_VERSION
ARG DDEV_DATABASE
RUN getent group tty || groupadd tty
RUN (groupadd --gid "$gid" "$username" || groupadd "$username" || true) && \
    (useradd -G tty -l -m -s "/bin/bash" --gid "$username" --comment '' --uid "$uid" "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --gid "$username" --comment '' "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --gid "$gid" --comment '' "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --comment '' "$username")

### DDEV-injected extra content

RUN mkdir -p /home/$username && chown $username /home/$username && chmod 600 /home/$username/.pgpass
ENV NVM_DIR=/home/$username/.nvm
RUN corepack enable
### DDEV-injected SQLite 3.45.1 is required for Drupal 11 tests, change the project type if you don't need this
RUN apt-get install -y /usr/local/sqlite3-drupal11/*.deb
ADD frankenphp.conf /etc/supervisor/conf.d
RUN chmod 644 /etc/supervisor/conf.d/frankenphp.conf
ADD webextradaemons.conf /etc/supervisor/conf.d
RUN chmod 644 /etc/supervisor/conf.d/webextradaemons.conf


### DDEV-injected composer update
RUN export XDEBUG_MODE=off; composer self-update --stable || composer self-update --stable || true; composer self-update --2 || log-stderr.sh composer self-update --2 || true


### From user Dockerfile /home/danielius/FuelCalculatorDrupal/.ddev/web-build/Dockerfile.frankenphp:
#ddev-generated
COPY --from=frankenphp /usr /usr
COPY --from=frankenphp /etc/frankenphp /etc/frankenphp
RUN ldconfig
ARG PHP_INI_DIR=/usr/local/etc/php
ARG PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ARG PHP_CPPFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"

# Don't run cleanup in install-php-extensions, because it removes build tools,
# and running install-php-extensions second time later is going to fail because of that.
# So we just remove the cleanup step from the script.
RUN sed -i '/^cleanup$/d' /usr/local/bin/install-php-extensions

ARG FRANKENPHP_DEFAULT_EXTENSIONS=""

ARG FRANKENPHP_CUSTOM_EXTENSIONS=""

# Install default extensions if not empty
RUN if [ -n "${FRANKENPHP_DEFAULT_EXTENSIONS}" ]; then \
        for ext in ${FRANKENPHP_DEFAULT_EXTENSIONS}; do \
            install-php-extensions "$ext" || true; \
        done; \
    fi

# Install user-defined extensions if any
RUN if [ -n "${FRANKENPHP_CUSTOM_EXTENSIONS}" ]; then \
        install-php-extensions ${FRANKENPHP_CUSTOM_EXTENSIONS}; \
    fi

RUN <<EOF
set -eu -o pipefail
chmod -fR ugo+w /usr/sbin /usr/bin /usr/local/etc/php

cp /etc/php/${DDEV_PHP_VERSION}/fpm/php.ini /usr/local/etc/php/php.ini

rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini

cat <<FILE_EOF > /usr/local/bin/enable_xdebug
#!/usr/bin/env bash
cp /etc/php/\${DDEV_PHP_VERSION}/mods-available/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
supervisorctl restart webextradaemons:frankenphp >/dev/null || true
echo "Enabled xdebug"
FILE_EOF

cat <<FILE_EOF > /usr/local/bin/disable_xdebug
#!/usr/bin/env bash
rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
supervisorctl restart webextradaemons:frankenphp >/dev/null || true
echo "Disabled xdebug"
FILE_EOF

cat <<FILE_EOF > /usr/local/bin/enable_xhprof
#!/usr/bin/env bash
rm -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
cp /etc/php/\${DDEV_PHP_VERSION}/mods-available/xhprof.ini /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini
supervisorctl restart webextradaemons:frankenphp >/dev/null || true
echo "Enabled xhprof with xhprof_mode=\${DDEV_XHPROF_MODE}"
case "\${DDEV_XHPROF_MODE}" in
  xhgui)
    echo "Use 'ddev xhgui' to launch browser for xhprof results."
    ;;
  *)
    echo "After each web request or CLI process you can see all runs, most recent first, at
\${DDEV_PRIMARY_URL}/xhprof"
    ;;
esac
FILE_EOF

cat <<FILE_EOF > /usr/local/bin/disable_xhprof
#!/usr/bin/env bash
rm -f /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini
supervisorctl restart webextradaemons:frankenphp >/dev/null || true
echo "Disabled xhprof"
FILE_EOF

EOF

### DDEV-injected folders permission fix
RUN chmod 777 /run/php /var/log
RUN mkdir -p /tmp/xhprof && chmod -R ugo+w /etc/php /var/lib/php /tmp/xhprof
