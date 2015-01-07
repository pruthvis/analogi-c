AnaLogi-c 0.1
Web interface for OSSEC based on AnaLogi v1.3
===
The goal of this fork of Analogi is to make available to the the community
updates made to resolve issues running Analogi in my environment:
	OSSEC 2.8.1
	PHP 5.5, with E_STRICT
	Debian 7.7 (wheezy)
	Apache 2.4
	MySQL 5.5

The original application was written for inhouse analysis work, released under
GPL to give something back by the folks at ECSC, http://www.ecsc.co.uk.

- all notes for Analogi-c will be in this file


Installation
===
This is a drop in replacement for the original Analogi.  For installation and
notes see:
	https://github.com/ECSC/analogi

1. Analogi requires an OSSEC installation with a server logging to MySQL.
Analogi must reside on a server running Apache and PHP.  The OSSEC MySQL
database can be on a local or remote server.  There are many guides on the
internet for help with these things.

The original Analogi installation instructions are in:
	INSTALL.txt

For info from the OSSEC project, see:
	http://ossec-docs.readthedocs.org/en/latest/manual/index.html
	http://www.ossec.net/doc/manual/output/mysql-database-output.html

2. Clone the git repo and edit the database settings
file (modify to suite your environment):
$ cd /var/www/html/
$ git clone https://github.com/ChrisDeFreitas/analogi-c
$ cp analogi-c/db_ossec.php.new analogi-c/db_ossec.php
$ vim analogi-c/db_ossec.php
	- change the following:
		define ('DB_USER_O', 'ossec_u');
		define ('DB_PASSWORD_O', 'Passw0rd');
		define ('DB_HOST_O', '127.0.0.1');
		define ('DB_NAME_O', 'ossec');

3. The Analogi web interface can be found at http://[your website url]/analogi-c/.

4. Configure analogy.  This is not required to run the app but you should at some
time check out the settings:
$ vim analogi-c/config.php


1. Master Branch, Commit 1
===
This commit fixes issues arising from the use of PHP's "E_STRICT"
error reporting. When I initially ran Analogi 1.3, PHP threw a few "Undefined
variable" errors.  It appears to be the result of having PHP's E_STRICT
error reporting turned on.

Searching PHP files for "//fixed:" will return all the changes.  Here is an
example of an update from management.php:
	//fixed: Undefined variable: $clientvsleveldebugstring in /srv/website/htdocs/analogi/management.php on line 362
	if(isset($clientvsleveldebugstring))
		...

User interface and functionality is unchanged from original Analogi 1.3. However,
there may be some functionality I can't see because its just broken.


2. UIUpdates Branch
===
- contains PHP fixes from Master branch
- HTML, CSS tweaks
- made wording more consistent
- added rule_id to a few lists
- screenshot: https://github.com/ChrisDeFreitas/analogi-c/tree/uiupdates/screenshots/index.png

Thanks/Links
===
Analogi
https://github.com/ECSC/analogi

ECSC
http://www.ecsc.co.uk/

OSSEC
http://www.ossec.net/

PHP Error Reporting
http://php.net/manual/en/migrating5.errorrep.php

Chris DeFreitas
http://datadevco.com