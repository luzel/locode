# Apache Web Server Technical Guide

## Introduction
Apache HTTP Server, commonly referred to as Apache, is an open-source and widely used web server software. It is known for its flexibility, robustness, and ability to handle high traffic. Apache supports various modules that extend its functionality, including SSL, URL rewriting, and authentication.

---

## Installing Apache on Ubuntu Server
To install Apache on an Ubuntu server, run the following command:

```bash
sudo apt update
sudo apt install apache2 -y
```

Once installed, Apache will automatically start running. You can check its status using:

```bash
sudo systemctl status apache2
```

---

## Managing Apache Service
To control the Apache service, use the following commands:

### Restart Apache
```bash
sudo systemctl restart apache2
```

### Stop Apache
```bash
sudo systemctl stop apache2
```

### Start Apache
```bash
sudo systemctl start apache2
```

### Enable Apache to Start on Boot
```bash
sudo systemctl enable apache2
```

### Disable Apache from Starting on Boot
```bash
sudo systemctl disable apache2
```

---

## VirtualHost Configuration
VirtualHost allows multiple domains to be hosted on a single Apache server. Each site can have its own configuration.

### Example VirtualHost Configuration (HTTP and HTTPS)
Create a configuration file for your site in `/etc/apache2/sites-available/`.

Example file: `/etc/apache2/sites-available/example.com.conf`

```apache
<Directory /var/www/example.com/public_html>
    AllowOverride All
    Require all granted
</Directory>
<VirtualHost *:80>
    ServerName example.com
    ServerAlias www.example.com
    DocumentRoot /var/www/example.com/public_html

    ErrorLog ${APACHE_LOG_DIR}/example.com_error.log
    CustomLog ${APACHE_LOG_DIR}/example.com_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName example.com
    ServerAlias www.example.com
    DocumentRoot /var/www/example.com/public_html

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/example.com.crt
    SSLCertificateKeyFile /etc/ssl/private/example.com.key

    ErrorLog ${APACHE_LOG_DIR}/example.com_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/example.com_ssl_access.log combined
</VirtualHost>
```

---

## Enabling and Disabling VirtualHosts

### Enable a Site
After creating the configuration file, enable the site using:

```bash
sudo a2ensite example.com.conf
sudo systemctl reload apache2
```

### Disable a Site
To disable the site, use:

```bash
sudo a2dissite example.com.conf
sudo systemctl reload apache2
```

---

## .htaccess Configuration
`.htaccess` is a configuration file used by Apache to control directory-level settings, such as URL rewriting, security, and caching.

### Example `.htaccess` for a PHP Project
```apache
# Enable URL rewriting
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Disable directory listing
Options -Indexes

# Set default index file
DirectoryIndex index.php

# Prevent access to .htaccess itself
<Files ".htaccess">
    Require all denied
</Files>
```

### Redirection in `.htaccess`
#### Permanent Redirect (301)
```apache
Redirect 301 /old-page.html /new-page.html
```

#### Temporary Redirect (302)
```apache
Redirect 302 /temp-page.html /new-temp-page.html
```

#### Internal Rewrite Example
```apache
RewriteEngine On
RewriteRule ^product/([0-9]+)$ product.php?id=$1 [L,QSA]
```

#### Redirect HTTP to HTTPS
```apache
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
```

---

## Basic Authentication
Apache allows password-protecting directories using Basic Authentication. This requires an `.htpasswd` file containing usernames and encrypted passwords.

### Enabling Basic Authentication in `.htaccess`
```apache
AuthType Basic
AuthName "Restricted Access"
AuthUserFile /etc/apache2/.htpasswd
Require valid-user
```

### Generating an `.htpasswd` File
Use the following command to create an `.htpasswd` file and add a user:

```bash
sudo htpasswd -c /etc/apache2/.htpasswd username
```

To add another user:

```bash
sudo htpasswd /etc/apache2/.htpasswd anotheruser
```

Ensure the `.htpasswd` file is protected:

```bash
sudo chmod 600 /etc/apache2/.htpasswd
```

---

## Conclusion
Apache is a powerful web server that supports VirtualHosts, SSL, and extensive customization. Managing sites using VirtualHost configuration, enabling and disabling them using `a2ensite` and `a2dissite`, and configuring `.htaccess` for URL rewriting, security, and redirections ensures flexible website hosting on a single server.

