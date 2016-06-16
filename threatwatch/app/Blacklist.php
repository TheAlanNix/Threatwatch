<?php namespace ThreatWatch;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model {

	// The IPs that belong to the Blacklist
	public function IPs()
	{
		return $this->belongsToMany('ThreatWatch\BlacklistIp');
	}

}
