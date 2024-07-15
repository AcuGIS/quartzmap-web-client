# QuartzMap Web Client 2.1.0

[![Documentation Status](https://readthedocs.org/projects/quartzmap/badge/?version=latest)](https://quartzmap.docs.acugis.com/en/latest/?badge=latest)



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

Be sure to set the hostname prior to installation if you plan to provision SSL using certbot.

hostnamectl set-hostname qpod.webgis1.com

```bash
$ git clone https://github.com/AcuGIS/quartzmap-web-client.git
$ cd quartzmap-web-client
$ ./installer/postgres.sh
$ ./installer/app-install.sh
```


 Optionally, provision and SSL certificate using:

 certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
	
## Documentation

QuartMap Web Client [Documentation](https://quartzmap.docs.acugis.com).
