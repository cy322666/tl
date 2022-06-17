<?php


namespace App\Services\amoCRM;

use Exception;
use Ufee\Amo\Oauthapi;

class Client
{
    /**
     * @throws Exception
     */
    public static function install(): Oauthapi
    {
        $amoApi = Oauthapi::setInstance([
            'domain'        => env('AMO_SUBDOMAIN'),
            'client_id'     => env('AMO_CLIENT_ID'),
            'client_secret' => env('AMO_CLIENT_SECRET'),
            'redirect_uri'  => env('AMO_REDIRECT_URL'),
        ]);

        try {

            $amoApi->account->toArray();

            return $amoApi;

        } catch (Exception $exception) {

            dd($amoApi->fetchAccessToken(env('AMO_AUTH_CODE')));
        }
    }
}
