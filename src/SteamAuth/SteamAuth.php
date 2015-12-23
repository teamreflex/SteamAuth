<?php

namespace Reflex\SteamAuth;

use GuzzleHttp\Client;
use Reflex\SteamAuth\Exceptions\ValidationRequestInvalid;

class SteamAuth
{
    /**
     * The GuzzleHTTP Client instance.
     *
     * @var GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * The base OpenID URL.
     *
     * @var string
     */
    protected $baseurl = 'https://steamcommunity.com/openid/login';

    /**
     * The OpenID select identifier.
     *
     * @var string
     */
    protected $selectIdentifier = 'http://specs.openid.net/auth/2.0/identifier_select';

    /**
     * Constructs the Guzzle client instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guzzle = new Client();
    }

    /**
     * Builds the Steam login URL and returns it.
     *
     * @param string $returnURL
     * @param string $realm
     * @return string
     */
    public function buildUrl($returnUrl = '', $realm = '')
    {
        if (!empty($returnUrl)) {
            if (!filter_var($returnUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('The return URL must be valid.');
            }
        } else {
            $returnUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . (empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST']) . $_SERVER['SCRIPT_NAME'];
        }

        if (!empty($realm)) {
            if (!filter_var($realm, FILTER_VALIDATE_URL)) {
                throw new Exception('The realm URL must be valid.');
            }
        } else {
            $realm = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . (empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST']);
        }

        return $this->baseurl . '?' . http_build_query([
            'openid.ns'         => 'http://specs.openid.net/auth/2.0',
            'openid.mode'       => 'checkid_setup',
            'openid.return_to'  => $returnUrl,
            'openid.realm'      => $realm,
            'openid.identity'   => $this->selectIdentifier,
            'openid.claimed_id' => $this->selectIdentifier
        ]);
    }

    /**
     * Validates the OpenID authentication request.
     *
     * @return string
     */
    public function validateRequest()
    {
        try {
            $params = [
                'openid.assoc_handle'   => $_GET['openid_assoc_handle'],
                'openid.signed'         => $_GET['openid_signed'],
                'openid.sig'            => $_GET['openid_sig'],
                'openid.ns'             => 'http://specs.openid.net/auth/2.0'
            ];
            $signed = explode(',', $_GET['openid_signed']);

            foreach ($signed as $item) {
                $value = $_GET['openid_' . str_replace('.', '_', $item)];
                $params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($value) : $value;
            }

            $params['openid.mode'] = 'check_authentication';

            $data = http_build_query($params);

            $request = $this->guzzle->request('POST', $this->baseurl, [
                'headers' => [
                    'Accept-Language'   => 'en',
                    'Content-Type'      => 'application/x-www-form-urlencoded',
                    'Content-Length'    => strlen($data)
                ],
                'body' => $data
            ]);

            if (preg_match('/is_valid:false/', $request->getBody())) {
                throw new ValidationRequestInvalid('The validation request returned invalid.');
            }

            if (preg_match('/http:\/\/steamcommunity.com\/openid\/id\/([0-9]+)/', $_GET['openid_claimed_id'], $matches) != 1) {
                throw new ValidationRequestInvalid('The request did not contain a SteamID64.');
            }

            $steamid = $matches[1];

            return $steamid;
        } catch (Exception $e) {
            throw new ValidationRequestInvalid($e->getMessage());
        }
    }
}
