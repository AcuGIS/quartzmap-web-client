#!/bin/bash -e

APP_DB='quartz'
APP_DB_PASS=$(< /dev/urandom tr -dc _A-Za-z0-9 | head -c32);
DATA_DIR='/var/www/data'
CACHE_DIR='/var/www/cache'
APPS_DIR='/var/www/html/apps'

PG_VER='16'
PG_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c32);

HNAME=$(hostname -f)

USE_SSL='no'

declare -x STEPS=('Initializing')
declare -x CMDS=('init_installer')

function install_postgresql(){
	RELEASE=$(lsb_release -cs)

	#3. Install PostgreSQL
	echo "deb http://apt.postgresql.org/pub/repos/apt/ ${RELEASE}-pgdg main" > /etc/apt/sources.list.d/pgdg.list
	wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -

	apt-get update -y || true

	apt-get install -y postgresql-${PG_VER} postgresql-client-${PG_VER} postgresql-contrib-${PG_VER} \
						python3-postgresql postgresql-plperl-${PG_VER} \
						postgresql-pltcl-${PG_VER} postgresql-${PG_VER}-postgis-3 \
						odbc-postgresql libpostgresql-jdbc-java
	if [ ! -f /usr/lib/postgresql/${PG_VER}/bin/postgres ]; then
		echo "Error: Get PostgreSQL version"; exit 1;
	fi

	ln -sf /usr/lib/postgresql/${PG_VER}/bin/pg_config 	/usr/bin
	ln -sf /var/lib/postgresql/${PG_VER}/main/		 	/var/lib/postgresql
	ln -sf /var/lib/postgresql/${PG_VER}/backups		/var/lib/postgresql

	systemctl start postgresql

	#5. Set postgres Password
	if [ $(grep -m 1 -c 'pg pass' /root/auth.txt) -eq 0 ]; then
		sudo -u postgres psql 2>/dev/null -c "alter user postgres with password '${PG_PASS}'"
		echo "pg pass: ${PG_PASS}" > /root/auth.txt
	fi

	#4. Add Postgre variables to environment
	if [ $(grep -m 1 -c 'PGDATA' /etc/environment) -eq 0 ]; then
		cat >>/etc/environment <<CMD_EOF
PGDATA=/var/lib/postgresql/${PG_VER}/main
CMD_EOF
	fi

	#6. Configure ph_hba.conf
	cat >/etc/postgresql/${PG_VER}/main/pg_hba.conf <<CMD_EOF
local	all all 							trust
host	all all 127.0.0.1	255.255.255.255	trust
host	all all 0.0.0.0/0					scram-sha-256
host	all all ::1/128						scram-sha-256
hostssl all all 127.0.0.1	255.255.255.255	scram-sha-256
hostssl all all 0.0.0.0/0					scram-sha-256
hostssl all all ::1/128						scram-sha-256
CMD_EOF
	sed -i.save "s/.*listen_addresses.*/listen_addresses = '*'/" /etc/postgresql/${PG_VER}/main/postgresql.conf
	sed -i.save "s/.*ssl =.*/ssl = on/" /etc/postgresql/${PG_VER}/main/postgresql.conf

	#10. Create Symlinks for Backward Compatibility from PostgreSQL 9 to PostgreSQL 8
	#ln -sf /usr/pgsql-9.4/bin/pg_config /usr/bin
	mkdir -p /var/lib/pgsql
	ln -sf /var/lib/postgresql/${PG_VER}/main /var/lib/pgsql
	ln -sf /var/lib/postgresql/${PG_VER}/backups /var/lib/pgsql

	#create SSL certificates
	if [ ! -f /var/lib/postgresql/${PG_VER}/main/server.key -o ! -f /var/lib/postgresql/${PG_VER}/main/server.crt ]; then
		SSL_PASS=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c32);
		if [ $(grep -m 1 -c 'ssl pass' /root/auth.txt) -eq 0 ]; then
			echo "ssl pass: ${SSL_PASS}" >> /root/auth.txt
		else
			sed -i.save "s/ssl pass:.*/ssl pass: ${SSL_PASS}/" /root/auth.txt
		fi
		openssl genrsa -des3 -passout pass:${SSL_PASS} -out server.key 2048
		openssl rsa -in server.key -passin pass:${SSL_PASS} -out server.key

		chmod 400 server.key

		openssl req -new -key server.key -days 3650 -out server.crt -passin pass:${SSL_PASS} -x509 -subj '/C=CA/ST=Frankfurt/L=Frankfurt/O=acuciva-de.com/CN=acuciva-de.com/emailAddress=info@acugis.com'
		chown postgres.postgres server.key server.crt
		mv server.key server.crt /var/lib/postgresql/${PG_VER}/main
	fi

	systemctl restart postgresql
}

function install_webmin(){
	
	if [ -f "/etc/letsencrypt/live/${HNAME}/cert.pem" ]; then
		cat /etc/letsencrypt/live/${HNAME}/cert.pem > /etc/webmin/miniserv.pem
		cat /etc/letsencrypt/live/${HNAME}/privkey.pem >> /etc/webmin/miniserv.pem
		echo "extracas=/etc/letsencrypt/live/${HNAME}/fullchain.pem" >> /etc/webmin/miniserv.conf
	fi
	
	systemctl restart webmin
	
  echo "deb http://download.webmin.com/download/repository sarge contrib" > /etc/apt/sources.list.d/webmin.list
  wget --quiet -qO - http://www.webmin.com/jcameron-key.asc | apt-key add -
  apt-get -y update
  apt-get -y install webmin
}

function install_proftpd(){
	apt-get -y install proftpd
	sed -i.save '
s/#DefaultRoot~/DefaultRoot ~/
s/# RequireValidShelloff/RequireValidShell off/' /etc/proftpd/proftpd.conf
	systemctl enable proftpd
	systemctl restart proftpd
	
	cat >/etc/sudoers.d/q2w <<CAT_EOF
www-data ALL = NOPASSWD: /usr/local/bin/create_ftp_user.sh, /usr/local/bin/delete_ftp_user.sh, /usr/local/bin/update_ftp_user.sh
CAT_EOF
}

function install_qat_application(){
	# 1. Install packages (assume PG is preinstalled)
	apt-get -y install apache2 php-{pgsql,zip,gd,simplexml,curl,fpm} \
		libapache2-mod-fcgid gdal-bin

	# setup apache
	a2enmod ssl headers expires fcgid cgi
	
	cp installer/apache2.conf /etc/apache2/sites-available/default-ssl.conf
	
	sed "s|\$DATA_DIR|$DATA_DIR|" < installer/qgis_apache2.conf > /etc/apache2/sites-available/qgis.conf
	
	for f in default-ssl 000-default; do
		sed -i.save "s/#ServerName example.com/#ServerName ${HNAME}/" /etc/apache2/sites-available/${f}.conf
	done
	
	a2ensite 000-default default-ssl qgis
	a2disconf serve-cgi-bin
	
	# switch to mpm_event to server faster and use HTTP2
	PHP_VER=$(php -version | head -n 1 | cut -f2 -d' ' | cut -f1,2 -d.)
	a2enmod proxy_fcgi setenvif http2
	a2enconf php${PHP_VER}-fpm
	a2enmod mpm_event
	
	systemctl reload apache2

	# 2. Create db
	su postgres <<CMD_EOF
createdb ${APP_DB}
createuser -sd ${APP_DB}
psql -c "alter user ${APP_DB} with password '${APP_DB_PASS}'"
psql -c "ALTER DATABASE ${APP_DB} OWNER TO ${APP_DB}"
CMD_EOF

	echo "${APP_DB} pass: ${APP_DB_PASS}" >> /root/auth.txt

	mkdir -p "${APPS_DIR}"
	mkdir -p "${CACHE_DIR}"
	mkdir -p "${DATA_DIR}"

	chown -R www-data:www-data "${APPS_DIR}"
	chown -R www-data:www-data "${CACHE_DIR}"
	chown -R www-data:www-data "${DATA_DIR}"

	cat >admin/incl/const.php <<CAT_EOF
<?php
define("DB_HOST", "localhost");
define("DB_NAME", "${APP_DB}");
define("DB_USER", "${APP_DB}");
define("DB_PASS", "${APP_DB_PASS}");
define("DB_PORT", 5432);
define("DB_SCMA", 'public');
define("APPS_DIR", "${APPS_DIR}");
define("CACHE_DIR", "${CACHE_DIR}");
define("SESS_USR_KEY", 'quartz_user');
define("SUPER_ADMIN_ID", 1);
define("DATA_DIR", "${DATA_DIR}");
?>
CAT_EOF
	
	cp -r . /var/www/html/
	chown -R www-data:www-data /var/www/html
	rm -rf /var/www/html/installer

	systemctl restart apache2

	# create group for all FTP users
	groupadd qatusers

	# install ftp user creation script
	for f in create_ftp_user delete_ftp_user update_ftp_user; do
		cp installer/${f}.sh /usr/local/bin/
		chown www-data:www-data /usr/local/bin/${f}.sh
		chmod 0550 /usr/local/bin/${f}.sh
	done
}

function install_qgis_server(){

	RELEASE=$(lsb_release -cs)
	wget --no-check-certificate --quiet -O /etc/apt/keyrings/qgis-archive-keyring.gpg https://download.qgis.org/downloads/qgis-archive-keyring.gpg

	# 3.28.x Firenze 				​-> URIs: https://qgis.org/ubuntu
	# 3.22.x Białowieża LTR	-> URIs: https://qgis.org/ubuntu-ltr
	cat >>/etc/apt/sources.list.d/qgis.sources <<CAT_EOF
Types: deb deb-src
URIs: https://qgis.org/ubuntu
Suites: ${RELEASE}
Architectures: amd64
Components: main
Signed-By: /etc/apt/keyrings/qgis-archive-keyring.gpg
CAT_EOF

	apt-get update -y || true
  apt-get install -y qgis-server libapache2-mod-fcgid
	
	if [ -d /etc/logrotate.d ]; then
		cat >/etc/logrotate.d/qgisserver <<CAT_EOF
/var/log/qgisserver.log {
	su www-data www-data
	size 100M
	notifempty
	missingok
	rotate 3
	daily
	compress
	create 660 www-data www-data
}
CAT_EOF
	fi
	
	mkdir -p ${DATA_DIR}/qgis
	chown www-data:www-data ${DATA_DIR}/qgis
	
	touch /var/log/qgisserver.log
	chown www-data:www-data /var/log/qgisserver.log
}

function install_certbot(){
	apt-get -y install apache2 python3-certbot-apache
	service apache2 restart
	certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
}

function install_postfix(){
	apt-get -y install postfix
}

function init_installer(){
	add-apt-repository -y universe
	apt-get -y update || true
	apt-get -y install wget unzip whiptail
}

function info_for_user(){
	#End message for user
	echo -e "Installation is now completed."
	echo -e "Complete the QAT Application installer at http://${HNAME}/admin/setup.php"
	echo -e "postgres and other passwords are saved in /root/auth.txt file"
}

function menu(){

	CHOICES=$(whiptail --separate-output --checklist "Choose options" 20 55 7 \
		"1." "Change Hostname" 		OFF \
		"2." "Install Let's Encrypt SSL" OFF \
		"3." "Install PostgreSQL" ON 	\
		"4." "Install Postfix" 		OFF \
		"5." "Install ProFTPD" 		ON 	\
		"6." "Install Webmin" 		ON 	\
		"7." "Install QGIS Server" ON 	\
		"8." "QAP Application" 		ON 	 3>&1 1>&2 2>&3)

if [ -z "${CHOICES}" ]; then
  echo "No option was selected (user hit Cancel or unselected all options)"
else
  for CHOICE in ${CHOICES}; do
    case "${CHOICE}" in
    "1.")
			HNAME=$(whiptail --inputbox "Please enter hostname" 10 100 3>&1 1>&2 2>&3)
			hostname -s "${HNAME}"
      ;;
		"2.")
			USE_SSL='yes'
      ;;
    "3.")
			STEPS+=("PostgreSQL")
			CMDS+=("install_postgresql")
      ;;
		"4.")
			STEPS+=("Postfix")
			CMDS+=("install_postfix")
      ;;
		"5.")
			STEPS+=("ProFTPd")
			CMDS+=("install_proftpd")
      ;;
		"6.")
			STEPS+=("Webmin")
			CMDS+=("install_webmin")
      ;;
		"7.")
			STEPS+=("QGIS Server")
			CMDS+=("install_qgis_server")
      ;;
		"8.")
			STEPS+=("QAT Application")
			CMDS+=("install_qat_application")
			
			if [ "${USE_SSL}" == "yes" ]; then
				STEPS+=("Let's Encrypt SSL")
				CMDS+=("install_certbot")
			fi
      ;;
    *)
      echo "Unsupported item ${CHOICE}!" >&2
      exit 1
      ;;
    esac
  done
fi
}

function progress_bar(){
  local MAX_STEPS=${#STEPS[@]}
  local BAR_SIZE="##########"
  local MAX_BAR_SIZE="${#BAR_SIZE}"
  local CLEAR_LINE="\\033[K"

  #tput civis -- invisible

  for step in "${!STEPS[@]}"; do
    perc=$((step * 100 / MAX_STEPS))
    percBar=$((perc * MAX_BAR_SIZE / 100))
    echo -ne "\\r- ${STEPS[step]} [ ]$CLEAR_LINE\\n"
    echo -ne "\\r[${BAR_SIZE:0:percBar}] $perc %$CLEAR_LINE"

    ${CMDS[$step]} 1>"/tmp/${CMDS[$step]}.log" 2>&1

    perc=$(((step + 1) * 100 / MAX_STEPS))
    percBar=$((perc * MAX_BAR_SIZE / 100))
    echo -ne "\\r\\033[1A- ${STEPS[step]} [✔]$CLEAR_LINE\\n"
    echo -ne "\\r[${BAR_SIZE:0:percBar}] $perc %$CLEAR_LINE"
  done
  echo ""

  #tput cnorm -- normal
}

################################################################################

touch /root/auth.txt
export DEBIAN_FRONTEND=noninteractive

if [ ! -d installer ]; then
	echo "Usage: ./installer/gui-installer.sh"
	exit 1
fi

progress_bar;
declare -x STEPS=()
declare -x CMDS=()

menu;
progress_bar;
info_for_user