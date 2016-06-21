<?php

namespace ThreatWatch\Http\Controllers;

use Illuminate\Http\Request;

use ThreatWatch\Blacklist;
use ThreatWatch\Setting;
use ThreatWatch\Http\Requests;

use Validator;

class ConfigurationController extends Controller
{
    /**
	 * Display a listing of the Blacklists we're using
	 *
	 * @return Response
	 */
	public function getIndex()
	{
        // Setup a data return array
        $return_data = array();

        // Get all blacklists
		$return_data['blacklists'] = Blacklist::all();

        // Get the status of the SMC
        if (!empty(getSetting('SMC_IP'))) {
            $return_data['smc_status'] = $this->getStealthwatchStatus();
        }

        // Get the status of OpenDNS
        if (!empty(getSetting('OPENDNS_KEY'))) {
            $return_data['opendns_status'] = $this->getOpenDNSStatus();
        }

		// Return the data to the view
		return view('configuration', $return_data);
	}

    public function getSettings()
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            print($setting->key);
        }
    }

    /**
	 * Function to delete a given Blacklist
	 *
	 * @return HTTP Redirect
	 */
	public function getDeleteBlacklist(Request $request)
	{
		// Delete the specified Blacklist
		$blacklist	= Blacklist::find($request->input('id'));
		$blacklist->delete();

		// Redirect to the Config page
		return redirect('/config');
	}

	/**
	 * Function to add a new Blacklist
	 *
	 * @return HTTP Redirect
	 */
	public function postBlacklist(Request $request)
	{
		// Create a new blacklist
		$blacklist 			= new Blacklist;
		$blacklist->name	= $request->input('name');
		$blacklist->url		= $request->input('url');
		$blacklist->regex	= $request->input('regex');
		$blacklist->save();

		// Redirect to the Config page
		return redirect('/config');
	}

    /**
     * Function to add/update the OpenDNS API Key
     *
     * @return HTTP Redirect
     */
    public function postOpendns(Request $request)
    {
        // Set the OPENDNS_KEY setting in the database
        setSetting('OPENDNS_KEY', $request->input('opendns_key'));

        // Redirect to the Config page
		return redirect('/config');
    }

    /**
     * Function to add/update the StealthWatch SMC
     *
     * @return HTTP Redirect
     */
    public function postStealthwatch(Request $request)
    {
        // Validate that the input is an IP Address
		$input = array(
			'Address'   => $request->input('ip_address'),
            'Username'  => $request->input('username'),
            'Password'  => $request->input('password'),
            'Domain'    => $request->input('domain')
		);

		$validator = Validator::make($input, [
            'Address'   => 'required|ip',
            'Username'  => 'required|alpha_dash',
            'Password'  => 'required',
            'Domain'    => 'required|numeric'
        ]);

		if ($validator->fails()) {
			return redirect('/config')->withErrors($validator);
		}

        if ($this->getStealthwatchStatus($input) == 200) {
            // Set the StealthWatch SMC settings in the database
            setSetting('SMC_IP',        $input['Address']);
            setSetting('SMC_USER',      $input['Username']);
            setSetting('SMC_PASS',      $input['Password']);
            setSetting('SMC_DOMAIN',    $input['Domain']);

            // Redirect to the Config page
    		return redirect('/config');
        } else {
            // Redirect to the Config page with error
    		return redirect('/config')->with('SMC_ERROR', 'Unable to connect to the SMC using the supplied information.');
        }
    }

    /**
     * Function to delete the StealthWatch SMC data
     *
     * @return HTTP Redirect
     */
    public function getDeleteStealthwatch(Request $request)
    {
        // Set the StealthWatch SMC settings in the database
        setSetting('SMC_IP',    '');
        setSetting('SMC_USER',  '');
        setSetting('SMC_PASS',  '');
        setSetting('SMC_DOMAIN','');

        // Redirect to the Config page
        return redirect('/config');
    }

    /**
     * Function to check the status of OpenDNS access.
     *
     * @return HTTP Response Code
     */
    private function getOpenDNSStatus()
    {
        $url = "https://investigate.api.opendns.com/domains/categorization/amazon.com";

        try {
			// Fetch the data from OpenDNS using cURL
			$curl = curl_init();
			$curl_headers = array('Authorization: Bearer ' . getSetting('OPENDNS_KEY'));
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER => $curl_headers,
				CURLOPT_TIMEOUT => 10
			));
			$response = curl_exec($curl);
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

            return $httpCode;
		} catch (exception $e) {
			// If we error out then print the message
			print('API Access Error: ' . $e->getMessage());
			exit;
		}
    }

    /**
     * Function to check the status of Stealthwatch access.
     *
     * @return HTTP Response Code
     */
    private function getStealthwatchStatus($credentials = null)
    {
        if (is_null($credentials)) {
            $credentials['Address']     = getSetting('SMC_IP');
            $credentials['Username']    = getSetting('SMC_USER');
            $credentials['Password']    = getSetting('SMC_PASS');
            $credentials['Domain']      = getSetting('SMC_DOMAIN');
        }

        // Build XML
		$request_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$request_xml .= "<soapenc:Envelope xmlns:soapenc=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
		$request_xml .= "\t<soapenc:Body>\n";
		$request_xml .= "\t\t<getDomain>\n";
        $request_xml .= "\t\t\t<domain id='" . $credentials['Domain'] . "' />";
        $request_xml .= "\t\t</getDomain>\n";
        $request_xml .= "\t</soapenc:Body>\n";
		$request_xml .= "</soapenc:Envelope>";

        // Build the URL to use when posting configuration to the SMC
		$SMC_URL = "https://" . $credentials['Address'] . "/smc/swsService/configuration";

		try {
			// Submit the provided XML to the SMC
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => "$request_xml",
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_TIMEOUT => 10,
				CURLOPT_URL => $SMC_URL,
				CURLOPT_USERPWD => $credentials['Username'] . ":" . $credentials['Password'],
			));
			$response = curl_exec($curl);
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

            return $httpCode;
		} catch (exception $e) {
			// If we error out then print the message
			print('Unable to Post to the SMC - Error: ' . $e->getMessage() . "\n");
			exit;
		}
    }
}
