<?php

namespace ThreatWatch\Http\Controllers;

use DB;

use Illuminate\Http\Request;

use ThreatWatch\Blacklist;
use ThreatWatch\Http\Requests;

use Validator;

class InvestigationController extends Controller
{
    /**
	 * Method to handle the HTTP request to view the Home page
	 *
	 * @return HTTP Response
	 */
	public function getIndex()
	{
		return view('welcome');
	}

	/**
	 * Method to handle the HTTP request to view IP Details
	 *
	 * @param  IP Address	$ip_address
	 * @return HTTP Response
	 */
	public function ipDetails($ip_address)
	{
		// Validate that the input is an IP Address
		$input = array(
			'ip_address'	=> $ip_address
		);

		$validator = Validator::make($input, [
            'ip_address'	=> 'required|ip'
        ]);

		if ($validator->fails()) {
			return view('ip.details')->withErrors($validator)->with('ip_address', $ip_address);
		}

        // Setup a data return array
        $return_data = array();

        // Provide the IP back to the View
        $return_data['ip_address']  = $ip_address;

        // If an OpenDNS Key has been specified, then get the relevant OpenDNS data
        if (!empty(getSetting('OPENDNS_KEY'))) {
    		// Fetch the OpenDNS data
    		$return_data['open_dns_history_data']     = $this->queryOpenDNS("https://investigate.api.opendns.com/dnsdb/ip/a/$ip_address.json");
    		$return_data['open_dns_malicious_data']   = $this->queryOpenDNS("https://investigate.api.opendns.com/ips/$ip_address/latest_domains");
            $return_data['threatgrid_data']           = $this->queryOpenDNS("https://investigate.api.opendns.com/samples/$ip_address?limit=100&sortBy=score");
        }

		// Return the data to the view
		return view('ip.details', $return_data);
	}

	/**
	 * Method to handle the HTTP request to view IP Summary
	 *
	 * @param  IP Address	$ip_address
	 * @return HTTP Response
	 */
	public function ipSummary($ip_address)
	{
        // Validate that the input is an IP Address
		$input = array(
			'ip_address'	=> $ip_address
		);

		$validator = Validator::make($input, [
            'ip_address'	=> 'required|ip'
        ]);

		if ($validator->fails()) {
			return view('ip.summary')->withErrors($validator)->with('ip_address', $ip_address);
		}

        // Setup a data return array
        $return_data = array();

        // Provide the IP back to the View
        $return_data['ip_address']  = $ip_address;

        // Fetch the Blacklist data
        $return_data['blacklists']    = Blacklist::all();
        $return_data['blacklisted']   = $this->queryBlacklistEntries($ip_address);

        // If an SMC has been specified, then get the Host Groups that the IP belongs to
        if (!empty(getSetting('SMC_IP'))) {
            $return_data['hostgroups'] = $this->getCurrentHostGroupNames($ip_address);
        }

        // If an OpenDNS Key has been specified, then get the relevant OpenDNS data
        if (!empty(getSetting('OPENDNS_KEY'))) {
    		// Fetch the OpenDNS data
    		$return_data['open_dns_history_data']     = $this->queryOpenDNS("https://investigate.api.opendns.com/dnsdb/ip/a/$ip_address.json");
    		$return_data['open_dns_malicious_data']   = $this->queryOpenDNS("https://investigate.api.opendns.com/ips/$ip_address/latest_domains");
            $return_data['threatgrid_data']           = $this->queryOpenDNS("https://investigate.api.opendns.com/samples/$ip_address?limit=100&sortBy=score");
        }

        if (!empty(getSetting('THREATSTREAM_KEY'))) {
    		// Fetch the ThreatStream data
    		$return_data['threat_stream_data'] = $this->queryThreatStreamIP($ip_address);
        }

		// Return the data to the view
		return view('ip.summary', $return_data);
	}

	/**
	 * Method to handle the HTTP request to view Domain Details
	 *
	 * @param  Domain String $domain
	 * @return HTTP Response
	 */
	public function domainDetails($domain)
	{
        // Setup a data return array
        $return_data = array();

        // Return the domain
        $return_data['domain'] = $domain;

        // Fetch the OpenDNS data
		$return_data['open_dns_domain_status_data']   = $this->queryOpenDNS('https://investigate.api.opendns.com/domains/categorization/' . $domain);
		$return_data['open_dns_domain_whois_data']    = $this->queryOpenDNS('https://investigate.api.opendns.com/whois/' . $domain);
		$return_data['open_dns_domain_security_data'] = $this->queryOpenDNS('https://investigate.api.opendns.com/security/name/' . $domain . '.json');

		// Fetch the OpenDNS category names
		$return_data['open_dns_categories']           = $this->queryOpenDNS('https://investigate.api.opendns.com/domains/categories/');

		$return_data['open_dns_domain_status_data']   = $return_data['open_dns_domain_status_data']->$domain;

		// Return the data to the view
		return view('domain.details', $return_data);
	}

	/**
	 * Method to handle the HTTP request to view the Host Groups we can add an IP to
	 *
	 * @param  HTTP Request	$request
	 * @return HTTP Response
	 */
	public function getHostGroupTree(Request $request)
	{
		// Get the IP Address we want to add
		$ip_address = $request->input('ip_address');

		// Validate the input
		$input = array(
			'ip_address'	=> $ip_address
		);

		$validator = Validator::make($input, [
            'ip_address'	=> 'required|ip'
        ]);

		if ($validator->fails()) {
			return view('hostgroups')->withErrors($validator);
		}

		// Set up a return string
		$return_string = null;

		// Fetch the Host Groups XML and clean it
		$return_xml = $this->submitXMLToSMC($this->getHostGroupsXML(), "configuration");
		$return_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'SOAPENC:'], '', $return_xml);

		// Load the XML for parsing
		$xml = simplexml_load_string($return_xml);
		$outside_hosts = $xml->Body->getHostGroupsResponse->domain->{'host-group-tree'}->{'outside-hosts'};

		// Get the current Host Groups for the given IP
		$current_host_groups = $this->getCurrentHostGroupIDs($ip_address);

		// Start building the HTML for the Host Group tree
		$return_string .= "<ul class='tree'>";

		// Foreach host group in the outside hosts, add it to the list, except for Countries
		foreach ($outside_hosts->children() as $host_group) {

			// If we have the Countries host group - skip it
			if ($host_group['name'] == 'Countries')
				continue;

			$this->iterateHostGroupHTML($host_group, $ip_address, $return_string, $current_host_groups);
		}

		$return_string .= "<li>";
		$return_string .= "<span class=\"pointer\" data-toggle=\"modal\" data-target=\".add-host-group\" onclick=\"updateAddHostGroup(0)\" title=\"Add SubGroup\">";
		$return_string .= "<i class=\"fa fa-plus-square-o\"></i> Add Host Group...";
		$return_string .= "</span>";
		$return_string .= "</li>";
		$return_string .= "</ul>";

		// Return the Host Groups view
		return view('hostgroups')->with('ip_address', $ip_address)->with('host_group_tree', $return_string);
	}

	/**
	 * Method to handle the HTTP request to add an IP to a Host Group
	 *
	 * @param  Parent Host Group		$host_group
	 * @param  IP Address of Host		$ip_address
	 * @param  Return String Reference 	$return_string
	 * @param  Array of Current Host Group IDs $current_host_groups
	 * @return N/A
	 */
	private function iterateHostGroupHTML($host_group, $ip_address, &$return_string, &$current_host_groups)
	{
		// Build the HTML for the Host Group tree
		if (in_array($host_group['id'], $current_host_groups)) {
			$return_string .= "<li>";
			$return_string .= "<i class=\"fa fa-check-square-o\"></i> " . $host_group['name'];
		} else {
			$return_string .= "<li>";
			$return_string .= "<i class=\"fa fa-square-o\"></i> ";
			$return_string .= "<a class=\"pointer\" href=\"/add-to-host-group?id=" . $host_group['id'] . "&ip_address=" . $ip_address . "\">" . $host_group['name'] . "</a>";
		}

		$return_string .= "<ul>";

		// Iterate through any sub-groups
		if ($host_group->count() > 0) {
			foreach ($host_group->children() as $child_host_group) {
				if ($child_host_group['id'] != '') {
					$this->iterateHostGroupHTML($child_host_group, $ip_address, $return_string, $current_host_groups);
				}
			}
		}

		$return_string .= "<li>";
		$return_string .= "<span class=\"pointer\" data-toggle=\"modal\" data-target=\".add-host-group\" onclick=\"updateAddHostGroup(" . $host_group['id'] . ")\" title=\"Add SubGroup\">";
		$return_string .= "<i class=\"fa fa-plus-square-o\"></i> Add Host Group...";
		$return_string .= "</span>";
		$return_string .= "</li>";
		$return_string .= "</ul>";
		$return_string .= "</li>";
	}

	/**
	 * Method to get the Host Group IDs that a specific IP belongs in
	 *
	 * @param  IP Address	$ip_address
	 * @return An Array of Host Group IDs
	 */
	private function getCurrentHostGroupIDs($ip_address)
	{
		$return_xml = $this->submitXMLToSMC($this->getCurrentHostGroupsXML($ip_address), "hosts");
		$return_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'SOAPENC:'], '', $return_xml);

		// Load the XML for parsing
		$xml = simplexml_load_string($return_xml);
		$host_group_ids = $xml->Body->getHostInformationResponse->{'host-information-list'}->{'host-information'}['host-group-ids'];

		$host_group_ids = explode(",", $host_group_ids);

		return $host_group_ids;
	}

	/**
	 * Method to look at the Host Groups an IP belongs in, and then return an array of the Host Group names
	 *
	 * @param  IP Address	$ip_address
	 * @return Array of Host Group Names
	 */
	private function getCurrentHostGroupNames($ip_address)
	{
		$return_array = array();

		// Fetch the Host Groups XML and clean it
		$return_xml = $this->submitXMLToSMC($this->getHostGroupsXML(), "configuration");
		$return_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'SOAPENC:'], '', $return_xml);

		// Load the XML for parsing
		$xml = simplexml_load_string($return_xml);
		$outside_hosts = $xml->Body->getHostGroupsResponse->domain->{'host-group-tree'}->{'outside-hosts'};

		// Get the current Host Groups for the given IP
		$current_host_groups = $this->getCurrentHostGroupIDs($ip_address);

		// Foreach host group in the outside hosts, add it to the list, except for Countries
		foreach ($outside_hosts->children() as $host_group) {

			if (in_array($host_group['id'], $current_host_groups))
				$return_array[] = $host_group['name'];

			$this->iterateHostGroupNames($host_group, $return_array, $current_host_groups);
		}

		return $return_array;
	}

	/**
	 * Method to iterate through a multi-tiered Host Group Array
	 *
	 * @param  Parent Host Group 			$host_group
	 * @param  Return Array Refernce		$return_array
	 * @param  Current Hosts Groups for IP	$current_host_groups
	 * @return XML
	 */
	private function iterateHostGroupNames($host_group, &$return_array, &$current_host_groups)
	{
		// If the Host Group has no subgroups then exit
		if ($host_group->count() > 0) {

			// Go through each Child group for the Parent group
			foreach ($host_group->children() as $child_host_group) {

				// Sometimes there's a blank ID for a Child group?
				if ($child_host_group['id'] != '') {

					// If the IP we're looking at is in this group, then add it to the list
					if (in_array($child_host_group['id'], $current_host_groups))
						$return_array[] = $child_host_group['name'];

					$this->iterateHostGroupNames($child_host_group, $return_array, $current_host_groups);
				}
			}
		}
	}

	/**
	 * Method to handle the HTTP request to add an IP to a Host Group
	 *
	 * @param  HTTP Request	$request
	 * @return HTTP Redirect
	 */
	public function getAddToHostGroup(Request $request)
	{
		// Get the IP Address and Host Group that we want to add
		$ip_address		= $request->input('ip_address');
		$host_group_id	= $request->input('id');

		// Validate that the input
		$input = array(
			'host_group_id'	=> $host_group_id,
			'ip_address'	=> $ip_address
		);

		$validator = Validator::make($input, [
			'host_group_id'	=> 'required|numeric',
            'ip_address'	=> 'required|ip'
        ]);

		if ($validator->fails()) {
			return view('hostgroups')->withErrors($validator)->with('ip_address', $ip_address);
		}

		// If we pass validation, then submit the changes to the SMC
		$this->submitXMLToSMC($this->addHostGroupIPRangeXML($ip_address, $host_group_id), "configuration");

		// Redirect back to the IP page
		return redirect('/ip/' . $ip_address)->with('success', 'IP Successfully Added to Host Group');
	}

	/**
	 * Method to add a new Host Group to StealthWatch
	 *
	 * @param  HTTP Request	$request
	 * @return HTTP Redirect
	 */
	public function postAddHostGroup(Request $request)
	{
		// Get the IP Address and Host Group that we want to add
		$parent_group_id	= $request->input('parent_group_id');
		$host_group_name	= $request->input('host_group_name');
		$host_ip_address	= $request->input('host_ip_address');

		// Validate that the input
		$input = array(
			'parent_group_id'	=> $parent_group_id,
			'host_group_name'	=> $host_group_name,
			'host_ip_address'	=> $host_ip_address
		);

		$validator = Validator::make($input, [
			'parent_group_id'	=> 'required|numeric',
            'host_group_name'	=> 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'host_ip_address'	=> 'required|ip'
        ]);

		if ($validator->fails()) {
			return view('hostgroups')->withErrors($validator)->with('ip_address', $host_ip_address);
		}

		// If we pass validation, then submit the change to the SMC
		$this->submitXMLToSMC($this->addHostGroupXML($host_group_name, $parent_group_id), 'configuration');

		// Return back to the Host Group list
		return redirect('/host-group-tree?ip_address=' . $host_ip_address)->with('success', 'Host Group Successfully Added');
	}

	/**
	 * Method to see if an IP is in the imported Blacklists
	 *
	 * @param  IP Address	$ip_address
	 * @return Blacklist Array Response
	 */
	private function queryBlacklistEntries($ip_address)
	{
        // Placeholder for the return variable
		$blacklisted = array();

        // Get the blacklists that we need to check
        $blacklists = Blacklist::all();

        // Create cURL array
        $curl_array = array();

        // Create curl_multi instance
        $curl_multi = curl_multi_init();

        // Create cURL requests for all the Blacklists
        foreach ($blacklists as $blacklist) {
            $curl_array[$blacklist->id] = curl_init($blacklist->url);
            curl_setopt($curl_array[$blacklist->id], CURLOPT_POST, false);
            curl_setopt($curl_array[$blacklist->id], CURLOPT_HEADER, false);
            curl_setopt($curl_array[$blacklist->id], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$blacklist->id], CURLOPT_TIMEOUT, 10);
        }

        // Add the cURL requests to the curl_multi instance
        foreach ($curl_array as $curl_request) {
            curl_multi_add_handle($curl_multi, $curl_request);
        }

        // Execute all cURL requests simultaneously, and continue when all are complete
        $running = null;
        do {
            curl_multi_exec($curl_multi, $running);
        } while ($running);

        // Process all requests and close all the handles
        foreach ($blacklists as $blacklist) {

            $httpCode = curl_getinfo($curl_array[$blacklist->id], CURLINFO_HTTP_CODE);
            $response = curl_multi_getcontent($curl_array[$blacklist->id]);

            // Make sure we got an HTTP/200 or else error out.
			if ($httpCode == 200) {

				// Match on all IPs in the blacklist text
				preg_match_all($blacklist->regex, $response, $ip_addresses);

                // Check to see if the IP is in the blacklist, and if so, add the blacklist ID to an array
                if (in_array($ip_address, $ip_addresses[0])) {
                    $blacklisted[] = $blacklist->id;
                }
			} else {
				print('Connection Failure. Status code: ' . $httpCode . ' / URL: ' . $blacklist->url);
				exit;
			}

            // Close the cURL handle
            curl_multi_remove_handle($curl_multi, $curl_array[$blacklist->id]);
        }

        // Close the curl_multi intance
        curl_multi_close($curl_multi);

        // Return an array of the blacklist IDs for which the provided IP was blacklisted
		return $blacklisted;
	}

	/**
	 * Method to fetch data from OpenDNS
	 *
	 * @param  URL	$url
	 * @return JSON Response
	 */
	private function queryOpenDNS($url)
	{
        // Get the data from OpenDNS and decode the JSON
        $opendns_response = $this->curlGetRequest($url, array('Authorization: Bearer ' . getSetting('OPENDNS_KEY')));
        $opendns_response = json_decode($opendns_response);

        // Return the JSON data
        return $opendns_response;
	}

	/**
	 * Method to generate XML to get all host groups from the SMC
	 *
	 * @return XML
	 */
	private function getHostGroupsXML()
	{
		// Build getHostGroups XML
		$return_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$return_xml .= "<soapenc:Envelope xmlns:soapenc=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$return_xml .= "\t<soapenc:Body>\n";
		$return_xml .= "\t\t<getHostGroups>\n";
		$return_xml .= "\t\t\t<domain id=\"" . getSetting('SMC_DOMAIN') . "\" />\n";
		$return_xml .= "\t\t</getHostGroups>\n";
		$return_xml .= "\t</soapenc:Body>\n";
		$return_xml .= "</soapenc:Envelope>";

		return $return_xml;
	}

	/**
	 * Method to generate XML to add an IP to a host group on the SMC
	 *
	 * @param  IP Address		$ip_address
	 * @param  Host Group ID	$group_id
	 * @return XML
	 */
	private function addHostGroupIPRangeXML($ip_address, $group_id)
	{
		// Build XML
		$return_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$return_xml .= "<soapenc:Envelope xmlns:soapenc=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$return_xml .= "\t<soapenc:Body>\n";
		$return_xml .= "\t\t<addHostGroupIPRange>\n";
		$return_xml .= "\t\t\t<host-group id=\"$group_id\" domain-id=\"" . getSetting('SMC_DOMAIN') . "\">\n";
		$return_xml .= "\t\t\t\t<ip-address-ranges>" . $ip_address . "</ip-address-ranges>\n";
		$return_xml .= "\t\t\t</host-group>\n";
		$return_xml .= "\t\t</addHostGroupIPRange>\n";
		$return_xml .= "\t</soapenc:Body>\n";
		$return_xml .= "</soapenc:Envelope>";

		return $return_xml;
	}

	/**
	 * Method to generate XML to get the current Host Groups for an IP (Busted)
	 *
	 * @param  IP Address	$ip_address
	 * @return XML
	 */
	private function getCurrentHostGroupsXML($ip_address)
	{
		// Build XML
		$return_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$return_xml .= "<soapenc:Envelope xmlns:soapenc=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$return_xml .= "\t<soapenc:Body>\n";
		$return_xml .= "\t\t<getHostInformation>\n";
		$return_xml .= "\t\t\t<host-information-filter domain-id=\"" . getSetting('SMC_DOMAIN') . "\">\n";
		$return_xml .= "\t\t\t\t<host-selection>\n";
		$return_xml .= "\t\t\t\t\t<ip-address-list-selection>\n";
		$return_xml .= "\t\t\t\t\t\t<ip-address value=\"" . $ip_address . "\" />\n";
		$return_xml .= "\t\t\t\t\t</ip-address-list-selection>\n";
		$return_xml .= "\t\t\t\t</host-selection>\n";
		$return_xml .= "\t\t\t</host-information-filter>\n";
		$return_xml .= "\t\t</getHostInformation>\n";
		$return_xml .= "\t</soapenc:Body>\n";
		$return_xml .= "</soapenc:Envelope>";

		return $return_xml;
	}

	/**
	 * Method to generate XML to add a host group on the SMC
	 *
	 * @param  Group Name		$group_name
	 * @param  Parent Group ID	$parent_id
	 * @return XML
	 */
	private function addHostGroupXML($group_name, $parent_id)
	{
		// Build XML
		$return_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$return_xml .= "<soapenc:Envelope xmlns:soapenc=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$return_xml .= "\t<soapenc:Body>\n";
		$return_xml .= "\t\t<addHostGroup>\n";
		$return_xml .= "\t\t\t<host-group domain-id=\"" . getSetting('SMC_DOMAIN') . "\" name=\"$group_name\" parent-id=\"$parent_id\">\n";
		$return_xml .= "\t\t\t</host-group>\n";
		$return_xml .= "\t\t</addHostGroup>\n";
		$return_xml .= "\t</soapenc:Body>\n";
		$return_xml .= "</soapenc:Envelope>";

		return $return_xml;
	}

	/**
	 * Method to send request XML to the SMC
	 *
	 * @param  XML Request  	$xml
	 * @param  SMC "Service"	$service
	 * @return XML Response
	 */
	private function submitXMLToSMC($xml, $service)
	{
		// Build the URL to use when posting configuration to the SMC
		$SMC_URL = "https://" . getSetting('SMC_IP') . "/smc/swsService/" . $service;

        // Get the SMC credentials to pass along
        $SMC_CREDENTIALS = getSetting('SMC_USER') . ":" . getSetting('SMC_PASS');

        // Submit the cURL criteria to our generic POST function
        $xml_response = $this->curlPostRequest($SMC_URL, array('Content-Type: application/x-www-form-urlencoded'), $xml, $SMC_CREDENTIALS);

        // Return the XML Response
        return $xml_response;
	}

	/**
	 * Generic GET cURL method
	 *
	 * @param  Request URL		$url
	 * @param  Request Headers	$headers
	 * @return HTTP Response
	 */
	private function curlGetRequest($url, $header = array())
	{
		try {
			// Fetch the data using cURL
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT => 10,
				CURLOPT_URL => $url,
			));
			$response = curl_exec($curl);
			$curlinfo = curl_getinfo($curl);
			curl_close($curl);

			// Make sure we got an HTTP/200 or else error out.
			if ($curlinfo['http_code'] >= 200 && $curlinfo['http_code'] < 300) {

				// Return the Response
				return $response;

			} else {
				print('cURL Connection Failure. Status code: ' . $curlinfo['http_code'] . ' URL: ' . $url);
			}
		} catch (exception $e) {
			// If we error out then print the message
			print('cURL Error: ' . $e->getMessage());
		}
	}

	/**
	 * Generic POST cURL method
	 *
	 * @param  Request URL		$url
	 * @param  Request Headers	$headers
	 * @param  Request XML		$post_xml
	 * @return HTTP Response
	 */
	private function curlPostRequest($url, $header = array(), $post_xml = null, $credentials = null)
	{
		try {
			// Fetch the data using cURL
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => "$post_xml",
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT => 10,
				CURLOPT_URL => $url,
                CURLOPT_USERPWD => $credentials
			));

			$response = curl_exec($curl);
			$curlinfo = curl_getinfo($curl);
			curl_close($curl);

			// Make sure we got an HTTP/200 or else error out.
			if ($curlinfo['http_code'] >= 200 && $curlinfo['http_code'] < 300) {

				// Return the Response
				return $response;

			} else {
				print('cURL Connection Failure. Status code: ' . $curlinfo['http_code'] . ' URL: ' . $url);
			}
		} catch (exception $e) {
			// If we error out then print the message
			print('cURL Error: ' . $e->getMessage());
		}
	}
}
