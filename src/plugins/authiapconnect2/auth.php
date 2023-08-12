<?php

require "validate_jwt.php";

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

        if (isset($headers['x-goog-iap-jwt-assertion'])) {
            return $headers['x-goog-iap-jwt-assertion'];
        }

        $devToken = getenv('IAP_DEV_TOKEN');
        if ($devToken) {
            return $devToken;
        }
        throw new Exception('No token found');
    }

    public function trustExternal($user, $pass, $sticky = false)
    {
        global $USERINFO;

        $sticky ? $sticky = true : $sticky = false; //sanity check
 
		if (!empty($_SESSION[DOKU_COOKIE]['auth']['info'])) {
			$USERINFO['name'] = $_SESSION[DOKU_COOKIE]['auth']['info']['name'];
			$USERINFO['mail'] = $_SESSION[DOKU_COOKIE]['auth']['info']['mail'];
			$USERINFO['grps'] = $_SESSION[DOKU_COOKIE]['auth']['info']['grps'];
			$_SERVER['REMOTE_USER'] = $_SESSION[DOKU_COOKIE]['auth']['user'];
			return true;
		}
		
        if (!empty($user)) {

            $token = $this->getIapToken();

            try {
                $data = validate_jwt($token, $this->getConf('iap_expected_audience'));
                $USERINFO = [
                    'name' => $data['gcip']['name'],
                    'mail' => $data['gcip']['email'],
                    'grps' => array_merge(explode(',',$data['gcip']['groups']), ['user'])
                ];
            } catch (Exception $e) {
                return false;
            }        

            $_SERVER['REMOTE_USER']                = $USERINFO['name'];
            $_SESSION[DOKU_COOKIE]['auth']['user'] = $USERINFO['name'];
            $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

            return true;
        }
        
        return false;
    }
}
