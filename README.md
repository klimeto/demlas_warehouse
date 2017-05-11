# DEMLAS warehouse code base
Data and metadata management system composed of the following components:
### 1. Discovery map portal
Web application developed in HTML / JavaScript using Bootstrap, Openlayers 3 and JQuery APIs offering users discovery and geo visualization options. 

Online app is available here: https://demlas.geof.unizg.hr/warehouse/portal/
### 2. Data uploader and manager
PHP, HTML and JavaScript web application capable to browse data in preconfigured root directory, create new dir structure, upload new files and create descriptive information - metadata using metadata editor described above. Datasets get uploaded as files on the server, plus in addition if geospatial data formats, e.g. GeoTIFF, ShapeFile, they get published in platform geoserver. Corresponding metadata encoded in Dublin Core XML get generated automatelly during file upload, which can later be updated by user using Metadata editor.

App is powered by [Evoluted Directory Listing Script](https://github.com/XnSger/EvoDire)

### 3. Metadata editor

Angular based app providing users a web form to collect descriptive information - metadata about information resources. Basic validation is ensured by mandatory input fields. Metadata can be downloaded as Dublin Core XML, after basic authentication saved in the DEMLAS warehouse as well as published in the DEMLAS catalogue.

Online app is available here: https://demlas.geof.unizg.hr/warehouse/editor/#?c=0:0:1

### 4. API

Scripts developed in PHP serve to execute batch processes as well as support client applications to work with the server side. 
### 5. Geoserver
WebGIS server based on [Geoserver](https://github.com/geoserver/geoserver) offering a provision of geospatial data using services standardized by Open Geospatial Consortium (OGC).  
### 6. Catalogue
Catalogue application based on [Geonetwork](https://github.com/geonetwork/core-geonetwork) integrated into warehouse via API used to collect and manage metadata about resources from the warehouse.
