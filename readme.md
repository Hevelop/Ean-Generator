Hevelop EanGenerator
=====================
Magento 1 auto generation ean 13 digits for products
 
Facts
-----
- version: 1.0.0
- extension key: Hevelop_EanGenerator
- [extension on GitHub](https://github.com/hevelop/ean-generator)

Description
-----------
This extension auto generate ean on before save event and make a full generation via cron job

Requirements
------------
- PHP >= 5.6.0

Compatibility
-------------
- Magento >= 1.4

Installation Instructions
-------------------------
1. `composer require hevelop/ean-generator`
2. Clear the cache, logout from the admin panel and then login again.
3. Configure and activate the extension under System - Configuration - Hevelop - Ean Generator.

Uninstallation
--------------
1. `composer remove hevelop/ean-generator`

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/hevelop/ean-generator/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Alex Bordin
[@bordeo](https://twitter.com/bordeo)

Licence
-------
[GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 (AGPL-3.0)](https://opensource.org/licenses/AGPL-3.0)

Copyright
---------
(c) 2016 Hevelop
