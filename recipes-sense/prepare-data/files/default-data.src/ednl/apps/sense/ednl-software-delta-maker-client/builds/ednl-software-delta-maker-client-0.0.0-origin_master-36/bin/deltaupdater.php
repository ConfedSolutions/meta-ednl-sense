#!/usr/bin/env php
<?php
/**
 * Sense Module DeltaUpdater
 *
 * PHP Version 5
 *
 * @category Executables
 * @package  EdnlSenseModuleDeltaUpdater
 * @author   Joël Morren <joel@exa.nl>
 * @license  (c) 2015 EDNL
 * @link     http://www.sensecloud.nl
 */
require_once __DIR__.'/../lib/class.DeltaUpdater.php';

$settingsargs = array(); 
if (file_exists(__DIR__.'/../etc/settings')) {//retrive settings from settings file(if it exists)
	$settings = file_get_contents(__DIR__.'/../etc/settings');
	$lines    = explode("\n", $settings);
	foreach ($lines as $line) {
		if (preg_match('/^EDNL_DeltaUpdater="(.*)"$/', trim($line), $match)) {
			$settingsargs = explode(" ", $match[1]);
		}
	}
}

$DMC = new DeltaUpdater($settingsargs);
$DMC->main();
