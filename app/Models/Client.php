<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\YClients;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    protected $primaryKey = 'client_id';
    protected $guarded  = [];
    protected $fillable = [
        'client_id',
        'name',
        'phone',
        'email',
        //'birth_date', TODO
        'spent',
        'company_id',
        'visits',
        'spent',
        'contact_id',
    ];

    public static function buildArrayForModel($arrayRequest = null)
    {
        Log::info(__METHOD__);
        
        if(!empty($arrayRequest['data']['client']['id'])) {

            $arrayForModel = [
                'company_id' => $arrayRequest['company_id'],
                'client_id' => $arrayRequest['data']['client']['id'],
                'name' => $arrayRequest['data']['client']['name'],
                'phone' => $arrayRequest['data']['client']['phone'],
            ];

            if (!empty($arrayRequest['data']['client']['email']))
                $arrayForModel = array_merge($arrayForModel, ['email' => $arrayRequest['data']['client']['email']]);

            if (!empty($arrayRequest['data']['client']['success_visits_count']))
                $arrayForModel = array_merge($arrayForModel, ['success_visits_count' => $arrayRequest['data']['client']['success_visits_count']]);

            return $arrayForModel;
        } else {
    
            Log::info("нет контакта в записи # ".$arrayRequest['resource_id']);
            
            exit;
        }
    }

    public static function getClient($requestArray)
    {
        Log::info(__METHOD__, $requestArray);
        
        $arrayForClient = self::buildArrayForModel($requestArray);
    
        Log::info('array for client', $arrayForClient);
    
        $client = Client::where('client_id', $arrayForClient['client_id'])->first();
        
        if(!$client)
            $client = Client::create($arrayForClient);
        else {
            $client->fill($arrayForClient);
            $client->save();
        }
        
        $yclient = YClients::getClient($client);

        unset($yclient['birth_date']);//TODO
        //$yclient['birth_date'] = $yclient['birth_date'] = '' ? null : $yclient['birth_date'];

        $client->fill($yclient);
        $client->save();

        return $client = Client::where('client_id', $arrayForClient['client_id'])->first();
    }

    public function getRouteKeyName()
    {
        return 'client_id';
    }

    public function records()
    {
        return $this->hasMany('App\Models\Record');
    }
}

