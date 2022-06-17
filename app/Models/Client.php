<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use App\Services\YClients;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Client extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'phone',
        'email',
        'birth_date',
        'spent',
        'company_id',
        'visits',
        'spent',
        'contact_id',
    ];

    public static function buildArrayForModel($arrayRequest = null)
    {
        if(!empty($arrayRequest['data']['client']['id'])) {

            $arrayForModel = [
                'company_id' => $arrayRequest['company_id'],
                'client_id'  => $arrayRequest['data']['client']['id'],
                'name'  => $arrayRequest['data']['client']['name'],
                'phone' => $arrayRequest['data']['client']['phone'],
            ];

            if (!empty($arrayRequest['data']['client']['email']))

                $arrayForModel = array_merge($arrayForModel, [
                    'email' => $arrayRequest['data']['client']['email']
                ]);

            if (!empty($arrayRequest['data']['client']['success_visits_count']))

                $arrayForModel = array_merge($arrayForModel, [
                    'success_visits_count' => $arrayRequest['data']['client']['success_visits_count']
                ]);

            return $arrayForModel;
        } else {

            Log::info("нет контакта в записи # ".$arrayRequest['resource_id']);

            exit;
        }
    }

    public static function createOrUpdate(SymfonyRequest $request): Model|Builder
    {
        return Client::query()
            ->updateOrCreate([
                'client_id'  => $request->data['client']['id'],
            ],[
                'company_id' => $request->company_id,
                'name'  => $request->data['client']['name'],
                'phone' => $request->data['client']['phone'],
                'email' => $request->data['client']['email'],
                'visits'=> $request->data['client']['success_visits_count'],
            ]);
    }

    public function records(): HasMany
    {
        return $this->hasMany('App\Models\Record');
    }
}

