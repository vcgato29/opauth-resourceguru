<?php
/**
 * ResourceGuru strategy for Opauth
 * NOTE: config needs a 'response_type'=>'code'
 * 'ResourceGuru' => array(
 *       'client_id' => '...',
 *       'client_secret' => '...',
 *       'response_type' => 'code',
 *       ),
 *
 * More information on Opauth: http://opauth.org
 *
 *
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.ResourceGuruStrategy
 * @license      MIT License
 */

/**
 * ResourceGuru strategy for Opauth
 *
 * @package			Opauth.ResourceGuruStrategy
 */
class ResourceGuruStrategy extends OpauthStrategy {

    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys, without predefining any default values.
     */
    public $optionals = array('redirect_uri', 'scope', 'state', 'response_type');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth2callback'
    );

    /**
     * Auth request
     */
    public function request() {
        $url = 'https://api.resourceguruapp.com/oauth/authorize';
        // ResourceGuru requires https but I don't use it on localhost
        // $this->strategy['redirect_uri'] = str_replace('http://','https://',$this->strategy['redirect_uri']);
        $params = array(
            'client_id' => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri']
        );

        foreach ($this->optionals as $key) {
            if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
        }

        $this->clientGet($url, $params);
    }

    /**
     * Internal callback, after OAuth
     */
    public function oauth2callback() {
        if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
            $code = $_GET['code'];
            $url = 'https://api.resourceguruapp.com/oauth/token';

            // ResourceGuru requires https but I don't have it on my dev instance
            //$this->strategy['redirect_uri'] = str_replace('http://','https://',$this->strategy['redirect_uri']);

            $params = array(
                'code' => $code,
                'client_id' => $this->strategy['client_id'],
                'client_secret' => $this->strategy['client_secret'],
                'redirect_uri' => $this->strategy['redirect_uri'],
                'grant_type'=>'authorization_code',
            );
            //if (!empty($this->strategy['state'])) $params['state'] = $this->strategy['state'];

            $response = $this->serverPost($url, $params, null, $headers);
            $results = json_decode($response);


            if (!empty($results) && !empty($results->access_token)){
                $user = $this->user($results->access_token);

                $this->auth = array(
                    'info' => array(),
                    'uid' => $user['id'],
                    'provider'=>'resourceguru',
                    'credentials' => array(
                        'token' => $results->access_token,
                        'refresh' => $results->refresh_token,
                        'expires' => date('c', time() + $results->expires_in)
                    ),
                    'raw' => array()
                );

                $this->callback();
            }
            else {
                $error = array(
                    'code' => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw' => array(
                        'response' => $response,
                        'headers' => $headers
                    )
                );

                $this->errorCallback($error);
            }
        }
        else {
            $error = array(
                'code' => 'oauth2callback_error',
                'raw' => $_GET
            );

            $this->errorCallback($error);
        }
    }


    /**
     * Queries resourceguru v1 API for user info
     *
     * @param string $access_token
     * @return array Parsed JSON results
     */
    private function user($access_token) {
        $user = $this->serverGet('https://api.resourceguruapp.com/v1/me', array('access_token' => $access_token), null, $headers);

        if (!empty($user)) {
            return $this->recursiveGetObjectVars(json_decode($user));
        }
        else {
            $error = array(
                'code' => 'userinfo_error',
                'message' => 'Failed when attempting to query GitHub v3 API for user information',
                'raw' => array(
                    'response' => $user,
                    'headers' => $headers
                )
            );

            $this->errorCallback($error);
        }
    }
}