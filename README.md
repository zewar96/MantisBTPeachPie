"# MantisBTPeachPie" - This is NOT working.  It's my learning project for PeachPie

Changes made from the core Mantis repo:

1. Converted all PHP files in the \library\rssbuilder to UTF-8 for a bug in PeachPie v1.0.0

2. Excluded test files due to missing PHPUnit_Framework_TestSuite (will add back when I get the phar added)
	\tests\rest\AllTests.php
	\tests\rest\RestBase.php
	\tests\Mantis\AllTests.php
	\tests\soap\AllTests.php
	\tests\Mantis\MantisCoreBase.php
	\tests\soap\SoapBase.php

3. added Parsedown from https://github.com/erusev/parsedown/blob/master/Parsedown.php for the followign plugin:
	\plugins\MantisCoreFormatting\tests\MarkdownTest.php	41	

## Build

- `php composer.phar install`
- dotnet run
