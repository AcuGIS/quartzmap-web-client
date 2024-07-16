qgis2threejs
=====

QuartzMap supports publishing of qgis2threejs as well as qgis2web. Currently, dynamic data sources are not supported.

To publish your qgis2threejs maps, simply FTP or upload as you would with qgis2web.

   .. image:: images/threejs-map-print.jpg

QuartzMap adds PDF Print functionality, as shown below.

   .. image:: images/threejs-map.jpg

To publsih your qgis2threejs maps, follow below:

Publishing
------------

1. In QGIS, open the Project you wish to publish and start qgis2threejs

2. Export your map to a location on your desktop as normal

3. Be sure to select "Enable the Viewer to run locally"

   .. image:: images/threejslocal.png

4. FTP the map using FTP or use the "Upload" function to upload.

5. Go to Maps > Add New

Give your map a name and description.

Select the qgis2threejs map you wish to publish.

Your qgis2threejs map is now published

  .. image:: images/threejs-map-print.jpg


Control Options
------------------------

qgis2threejs offers two options for map controls.

Selecting "3D Viewer with data-gui panel" will produces the controls shown below:

  .. image:: images/threejs-right.png

Selecting "3D Viewer" will produces the controls shown below:

 .. image:: images/threejs-left.png


