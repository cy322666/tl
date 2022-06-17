<?php

namespace App\Services;

use App\Models\Client;
use App\Models\YClients as YC;

class YClients
{
    public static function instance(): YC
    {
        $yclients = new YC(env('YC_PARTNER_TOKEN'));
        $yclients->getAuth(env('YC_LOGIN'), env('YC_PASSWORD'));

        return $yclients;
    }

    public static function getClient(Client $client): array
    {
        $yclients = self::instance();

        return $yclients->getClient(
            $client->company_id,
            $client->client_id,
            env('YC_USER_TOKEN')
        );
    }
}
