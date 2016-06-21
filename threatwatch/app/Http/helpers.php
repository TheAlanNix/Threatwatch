<?php

/**
 * A function that gets a given Setting's value
 *
 * @var 	name		A string representing the Setting's 'key'
 * @return	value	A string representing the Setting's 'value'
 */
function getSetting($name)
{
	// Get the current Setting for the given Key
	$setting = \ThreatWatch\Setting::where('name', $name)->first();

	// If the Setting is empty, then return NULL, otherwise, return the Key
	if (empty($setting)) {
		return null;
	} else {
		return $setting->value;
	}
}

/**
 * A function that sets a given Setting's value
 *
 * @var	name	A string representing the Setting's 'key'
 * @var	value	A string representing the Setting's 'value'
 */
function setSetting($name, $value)
{
	// Get the current Setting for the given Key
	$setting = \ThreatWatch\Setting::where('name', $name)->first();

	// If there was no previous setting, then make a new one, else update
	if (empty($setting)) {
		// Make a new Setting
		$setting = new \ThreatWatch\Setting;
		$setting->name	= $name;
		$setting->value	= $value;
		$setting->save();
	} else {
		// Update the existing Setting
		$setting->value	= $value;
		$setting->save();
	}
}
