# LinuxBot mini en Telegram ([@LinuxLat_Bot](http://t.me/linuxlat_bot))

*Es __mini__ porque la versión completa la estoy recuperando*

* Creado por Gottesman ([en telegram](http://t.me/gottesman))

* UBICAR TODOS LOS ARCHIVOS EN /var/www/html

## Requisitos de ejecución

 apache2
 openssl
 php
 php_mbstring
 php_curl
 
 Ejemplo en debian:
 
 `apt-get install apache2 openssl php php_mbstring php_curl`
 

 - Tener configurado correctamente HTTPS con su certificado con URL de la IP,
si no se tiene, seguir la guia como **ROOT** en **BASH**:

 	1. Ejecutar: `cd /var/www`
	
 	2. Ejecutar:
 		`openssl req -newkey rsa:2048 -sha256 -nodes -keyout YOURPRIVATE.key -x509 -days 365 -out YOURPUBLIC.pem`

 		 Poner en "Common Name (e.g. server FQDN or YOUR name)" la dirección IP pública donde está el bot, lo demás es opcional 

	3. Ejecutar: `a2ensite default-ssl.conf`
	
	4. Abrir `/etc/apache2/sites-enabled/default-ssl.conf` con el editor que se quiera.
	
	5. Cambiar las lineas
	      `SSLCertificateFile ********`
	      `SSLCertificateKeyFile ********`
	     a
	      `SSLCertificateFile /var/www/YOURPUBLIC.pem`
	      `SSLCertificateKeyFile /var/www/YOURPRIVATE.key`

	6. Reiniciar el servicio de Apache
	
	7. Editar el archivo "config.php" llenando los campos adecuadamente
	
	8. Visitar http://IP/iniciar.php	(Cambiar IP por la IP pública del bot)
	
	9. Eso debería ser todo
 

- Tener la API de Open Weather Map (https://openweathermap.org/)
