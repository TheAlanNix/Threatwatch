<?php

/**
 * A function that gets a given Setting's value
 *
 * @var 	key		A string representing the Setting's 'key'
 * @return	value	A string representing the Setting's 'value'
 */
function getSetting($key)
{
	// Get the current Setting for the given Key
	$setting = \ThreatWatch\Setting::find($key);

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
 * @var	key		A string representing the Setting's 'key'
 * @var	value	A string representing the Setting's 'value'
 */
function setSetting($key, $value)
{
	// Get the current Setting for the given Key
	$setting = \ThreatWatch\Setting::find($key);

	// If there was no previous setting, then make a new one, else update
	if (empty($setting)) {
		// Make a new Setting
		$setting = new \ThreatWatch\Setting;
		$setting->key	= $key;
		$setting->value	= $value;
		$setting->save();
	} else {
		// Update the existing Setting
		$setting = $setting->first();
		$setting->value	= $value;
		$setting->save();
	}
}
