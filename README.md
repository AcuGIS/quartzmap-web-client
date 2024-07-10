# QuartzMap

![QuartzMap](QuartzMap.png)

## Transform your qgis2web maps into secure, dyanmic maps.

![QuartzMap](QuartzMap-Main.png)


Features
- Maps
	- Publish using qgis2web plugin
  	- Connect to PostGIS
	- Connecto GeoServer
	- QGIS Print Layouts (or basic Browser Print)
	- Advertise WMS, WFS, WMTS, etc...	
- Users
	- Users and Groups
	- Map level permissions
	- Multi-User Portal
 	- Optional self-registration

## Install
Install on Ubuntu 22 or 24

```bash
	$ git clone https://github.com/AcuGIS/quartzmap.git
	$ cd quartzmap
	$ ./installer/postgres.sh
	$ ./installer/app-install.sh
```


 Optionally, provision and SSL certificate using:

 certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
	

Run setup http://domain.com/admin/setup.php
