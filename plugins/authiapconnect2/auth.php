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
        if (isset($headers['x-goog-iap-jwt-assertion'])) {
            return $headers['x-goog-iap-jwt-assertion'];
        }

        return 'TEST-TOKEN'; // TODO: remove
        throw new Exception('No token found');
    }

    /**
     * Get groups from Connect2
     * @param string $user
     * @return array
     */
    private function getUserGroups($user)
    {
        // Get request to Connect2
        $url = $this->getConf('groups_endpoint');
        // Replace ':email' with $user
        $url = str_replace(':email', $user, $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        print_r($response);

        if ($httpcode != 200) {
            throw new Exception('Could not get groups');
        }

        $groups = json_decode($response, true);

        return $groups;
    }

    public function trustExternal($user, $pass, $sticky = false)
    {
        global $USERINFO;

        try {
            // TODO: use token instead of hardcoded username
            $token = $this->getIapToken();
            print_r($token);

            // Get user groups from Connect2
            $user = 'joepj@ch.tudelft.nl';
            $groups = $this->getUserGroups($user);

            // $USERINFO = $this->getUserData($user);
            $USERINFO = [
                'name' => 'Joep de Jong',
                'mail' => 'joepj@ch.tudelft.nl',
                'grps' => $groups,
            ];

            $_SERVER['REMOTE_USER']                = $user;
            $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
            $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
