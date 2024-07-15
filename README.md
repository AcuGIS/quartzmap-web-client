# QuartzMap Web Client 2.1.0

![QuartzMap](QuartzMap.png)

## Transform your qgis2web maps into secure, dyanmic maps.

![QuartzMap](QuartzMap-Main.png)


- QuartzMap Features

	- Publish Using qgis2web Plugin
	- Private and Public Maps
	- Connect Layers to PostGIS
	- Connect Layers to GeoServer
	- QGIS Print Layouts (or basic Browser Print)
	- Advertise WMS, WFS, WMTS, etc...	
	- Info Box
	- Opacity Controls
	- Customizable Portal
	- Users and Groups
	- Map Level permissions
	- Multi-User Portal
 	- Optional self-registration
	- Publish qgis2threejs (Static Only)

## Install
Install on Ubuntu 22 or 24

Be sure to set the hostname if you plan to provision SSL using certbot.

```bash
    $ hostnamectl set-hostname qpod.webgis1.com
	$ git clone https://github.com/AcuGIS/quartzmap.git
	$ cd quartzmap
	$ ./installer/postgres.sh
	$ ./installer/app-install.sh
```


 Optionally, provision and SSL certificate using:

 certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
	

Run setup http://domain.com/admin/setup.php
