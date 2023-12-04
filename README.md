# AuthJoomla 2023

A Joomla-MediaWiki authentication bridge.

This project consists of two parts:

* `User - MediaWiki Login`: A Joomla plugin which sets a cookie within the users web browser.
* `AuthJoomla2023`: A MediaWiki extension which reads the cookie and automatically logs the user in.

## Notes

A valid Joomla session allows access to the wiki, without having to log in into the wiki.
A wiki user profile is automatically created for the user on the first visit.

This project is a successor of [AuthJoomla2](https://www.mediawiki.org/wiki/Extension:AuthJoomla2) by [Harold Prins](https://www.haroldprins.nl/).
The Joomla extension is mostly unchanged, but the MediaWiki extension is a complete rewrite by
[Christian d'Heureuse](https://www.inventec.ch/chdh) in 2023.

The configuration parameters are mostly the same as in AuthJoomla2.

## Joomla configuration

To install the Joomla plugin, create a ZIP file from the two files in the `joomla` directory and upload and install
this zip file as a Joomla extension.

Configure the following parameters in the Joomla backend under "Extensions" / "Plugins".

* Mediawiki Secret Word: (choose your own secret word)
* Mediawiki Cookie Domain: empty
* Mediawiki Cookie Prefix: database name + "_mw_"
* Mediawiki Cookie Path: "/"
* Check New Usernames: off
* Username Regex: empty
* Regex Error Message: empty

## MediaWiki configuration

Copy the files from the `mediawiki` directory to `wikifiles/extensions/AuthJoomla2023`.

The following parameters must be set in LocalSettings.php:

```
$wgGroupPermissions['*']['read'] = false;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['autocreateaccount'] = true;

$wgAuthJoomla_security_key   = '(secret word)';     // same as in Joomla
$wgAuthJoomla_TablePrefix    = 'jos_';              // Joomla table name prefix

$wgAuthJoomla_MySQL_Host     = 'localhost';         // Joomla MySQL host name
$wgAuthJoomla_MySQL_Username = 'db-user';           // Joomla MySQL user name
$wgAuthJoomla_MySQL_Password = 'db-password';       // Joomla MySQL Password
$wgAuthJoomla_MySQL_Database = 'db-name';           // Joomla MySQL database name

wfLoadExtension('AuthJoomla2023');
```

Edit the special Wiki page `MediaWiki:Loginreqpagetext` and add a link to your Joomla login page.
