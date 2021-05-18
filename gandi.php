<?php

/**
 * Gandi Password Driver
 *
 * Payload is json string containing username, oldPassword and newPassword
 * Return value is a json string saying result: true if success.
 *
 * @author Frederic ALEX <fredy@mezimail.com>
 * @version 1.0.0
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 *
 * You need to define theses variables in plugin/password/config.inc.php
 *
 * @param $config['password_driver'] = 'gandi'; // use gandi as driver
 * @param $config['password_gandi_api_apikey'] = ''; // put apikey number from Gandi server
 * @param $config['password_gandi_api_email_domain'] = ''; // put domain name Gandi server
 * @param $config['password_minimum_length'] = 8; // select same number as in Gandi server
 */

class rcube_gandi_password_helper
{
	protected $GandiApiKey;
	protected $GandiApiEmailDomain;
 
	public function __construct($GandiApiEmailDomain,$GandiApiKey){
		$this->GandiApiEmailDomain = $GandiApiEmailDomain;
		$this->GandiApiKey = $GandiApiKey;
	}
/**
 * Get the Mailbox ID and return it as a string (ID comes from your GANDI Domain Mailboxes).
 * 
 * This takes the $address of a user in your domain and returns a string representing his ID. 
 * @param mixed $address Full Address Email (i.e. user@domain.com)
 * @author Frederic ALEX <fredy@mezimail.com>
 * @version ${1:1.0.0
 * @return String
 */
	public function getMailboxId($address){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.gandi.net/v5/email/mailboxes/".$this->GandiApiEmailDomain,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"authorization: Apikey ".$this->GandiApiKey
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
            return PASSWORD_CONNECT_ERROR;
		} else {
			$decoded = json_decode($response, true);
			if (!is_array($decoded)) {
				return PASSWORD_CONNECT_ERROR;
			}
			foreach($decoded as $k0 => $v0) {
				foreach($v0 as $k1 => $v1) {
					if($k1=="address" AND $v1==$address) {
						return $v0["id"];
					}
				}
			}
		}
	}
}

class rcube_gandi_password
{
/**
 * Save the new password for the currently logged in RC User.
 * 
 * This takes the $curpass (current password) and the new $passwd for the currently logged in RC User. 
 * @param mixed $curpass Current Password of the logged in RC User.
 * @param mixed $passwd New Password for the logged in RC User.
 * @author Frederic ALEX <fredy@mezimail.com>
 * @version ${1:1.0.0
 * @return String
 * if ($err) {
 *           return PASSWORD_CONNECT_ERROR;
 *       }
 *       return PASSWORD_SUCCESS;
 */
    public function save($curpass, $passwd)
    {
		// INIT AND CONFIGS
		$rcmail           = rcmail::get_instance();
		$GandiApiKey     = $rcmail->config->get('password_gandi_api_apikey');
		$GandiApiEmailDomain     = $rcmail->config->get('password_gandi_api_email_domain');
		$RoundCubeUsername = $_SESSION['username'];

		// Instantiate class rcube_gandi_password_helper to access the helper functions of Gandi Driver
		$rcube_gandi = new rcube_gandi_password_helper($GandiApiEmailDomain,$GandiApiKey);

        // Get mailbox ID
        $mailboxid = $rcube_gandi->getMailboxId($RoundCubeUsername);

        // Encode json with new password
        $ret['password'] = $passwd; // new password
        $encoded         = json_encode($ret);

        // Call HTTP API Gandi To PATCH the new Password
        $curl = curl_init();

		// CHANGE $mailboxid PASSWORD
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://api.gandi.net/v5/email/mailboxes/" . $GandiApiEmailDomain . "/" . $mailboxid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "PATCH",
            CURLOPT_POSTFIELDS     => "" . $encoded . "",
            CURLOPT_HTTPHEADER     => array(
                "Authorization: Apikey " . $GandiApiKey,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return PASSWORD_CONNECT_ERROR;
        }

        return PASSWORD_SUCCESS;
    }
}
