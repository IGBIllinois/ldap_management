How to install archive_accounting

Dependencies
1. PHP
2. PHP LDAP

Installation
1. Create an alias in apache pointing to html folder
2. Give apache read access to the web folder
3. Edit /conf/settings.inc.php to reflect your settings
4. To /etc/crontab add

0 2 * * * root php /path/to/bin/expiring_users_email.php
