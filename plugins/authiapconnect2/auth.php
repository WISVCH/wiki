<?php

use dokuwiki\Logger;
use dokuwiki\Utf8\Sort;

/**
 * Plaintext authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Chris Smith <chris@jalakai.co.uk>
 * @author     Jan Schumann <js@schumann-it.com>
 */
class auth_plugin_authiapconnect2 extends DokuWiki_Auth_Plugin
{

    public function __construct()
    {
        parent::__construct(); // for compatibility
        $this->cando['external'] = true;
        $this->success = true;
    }

    /**
     * Get token from IAP header
     * @return string
     */
    private function getIapToken()
    {
        $headers = apache_request_headers();
        if (isset($headers['X-Goog-IAP-JWT-Assertion'])) {
            return $headers['X-Goog-IAP-JWT-Assertion'];
        }

        $devToken = getenv('IAP_DEV_TOKEN');
        if ($devToken) {
            return $devToken;
        }
        throw new Exception('No token found');
    }

    /**
     * Get user data from Connect2
     * @param string $token
     * @return array
     */
    private function getUserDataFromToken($token)
    {
        // Get request to Connect2
        $url = $this->getConf('groups_endpoint');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'X-Goog-IAP-JWT-Assertion: ' . $token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode != 200) {
            throw new Exception('Could not get user data');
        }

        return json_decode($response, true);
    }


    /**
     * Validate user data from Connect2
     * @param array $data
     * @return bool
     */
    private function validateUserData($data)
    {
        // Check if data has email and groups
        if (!isset($data['email'])) {
            throw new Exception('No email found');
        }

        if (!isset($data['groups'])) {
            throw new Exception('No groups found');
        }

        return true;
    }

    public function trustExternal($user, $pass, $sticky = false)
    {
        global $USERINFO;

        $token = $this->getIapToken();

        // Get user data from Connect2
        $data = $this->getUserDataFromToken($token);
        if (!$this->validateUserData($data)) {
            return false;
        }

        $USERINFO = [
            'name' => str_replace('@ch.tudelft.nl', '', $data['email']),
            'mail' => $data['email'],
            'grps' => $data['groups']
        ];

        $_SERVER['REMOTE_USER']                = $USERINFO['name'];
        $_SESSION[DOKU_COOKIE]['auth']['user'] = $USERINFO['name'];
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

        return true;
    }
}
