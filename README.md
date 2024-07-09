# QuartzMap

Transform your qgis2web maps into secure, dyanmic maps.

![GeoSync](QuartzMap.png)


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
  
	$ cd qgis2web_app
	$ ./installer/postgres.sh
	$ ./installer/app-install.sh
	

Run setup http://domain.com/admin/setup.php
