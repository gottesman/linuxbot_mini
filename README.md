# LinuxBot mini en Telegram ([@LinuxLat_Bot](http://t.me/linuxlat_bot))

*Es __mini__ porque la versión completa la estoy recuperando*

* Creado por [@Gottesman](http://t.me/gottesman)

* Recomiendo el siguiente servicio de VPS: **[iniz.com](https://iniz.com/)** con ubicación en **Ámsterdam**

* UBICAR TODOS LOS ARCHIVOS EN /var/www/html

## Requisitos de ejecución

* Apache2, OpenSSL y PHP
 
 Ejemplo en Debian y Ubuntu:
 
 `apt-get install apache2 openssl php php_mbstring php_curl`
 
* Puerto 443 abierto en Firewalls y Router

 Para abrir puerto 443 en Debian es:
 `iptables -A INPUT -p tcp -m tcp --dport 443 -j ACCEPT`
 
 O en Ubuntu:
 `ufw allow 443`
 
* Se sugiere utilizar **fail2ban**

 Para instalar en Debian y Ubuntu
 `apt get install fail2ban`
 
 Configurar siguiendo la guía:
 
 1. Abrir con un editor de textos (como VIM o NANO) el archivo `/etc/fail2ban/jail.d/defaults-*.conf` y llenar con esta configuración:
	
```
[DEFAULT]
ignoreip = 127.0.0.1/8 ::1 149.154.160.0/22 149.154.164.0/22 91.108.4.0/22 91.108.56.0/22 91.108.8.0/22 95.161.64.0/20

[sshd]

enabled  = true
maxretry = 5
bantime  = 10800
port     = ssh
filter   = sshd
logpath  = /var/log/auth.log

[apache]
enabled  = true
port     = http,https
filter   = apache-auth
logpath  = /var/log/apache*/*error.log
maxretry = 6

[apache-noscript]

enabled  = true
port     = http,https
filter   = apache-noscript
logpath  = /var/log/apache*/*error.log
maxretry = 6

[apache-overflows]

enabled  = true
port     = http,https
filter   = apache-overflows
logpath  = /var/log/apache*/*error.log
maxretry = 2

[http-get-dos]
enabled = true
port = http,https
filter = http-get-dos
logpath = /var/log/apache*/*access.log
maxretry = 300
findtime = 300
bantime = 600
action = iptables[name=HTTP, port=http, protocol=tcp]
```

* Tener configurado correctamente HTTPS con su certificado con URL de la IP, si no se tiene, seguir la guía:

 	1. Ejecutar: `cd /var/www`
	
 	2. Ejecutar:
 		`openssl req -newkey rsa:2048 -sha256 -nodes -keyout YOURPRIVATE.key -x509 -days 365 -out YOURPUBLIC.pem`

 		 Poner en "Common Name (e.g. server FQDN or YOUR name)" la dirección IP pública donde está el bot, lo demás es opcional 

	3. Ejecutar: `a2ensite default-ssl.conf`
	
	4. Abrir `/etc/apache2/sites-enabled/default-ssl.conf` con el editor que se quiera.
	
	5. Cambiar las lineas
	      ```
SSLCertificateFile ********
SSLCertificateKeyFile ********
	      ```
	     a
	      ```
SSLCertificateFile /var/www/YOURPUBLIC.pem
SSLCertificateKeyFile /var/www/YOURPRIVATE.key
	      ```

	6. Reiniciar el servicio de Apache
	
	7. Editar el archivo "config.php" llenando los campos adecuadamente
	
	8. Visitar http://IP/iniciar.php	(Cambiar IP por la IP pública del bot)
	
	9. Eso debería ser todo
 

* Tener la API de Open Weather Map (https://openweathermap.org/)
