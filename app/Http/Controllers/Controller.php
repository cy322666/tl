<?php

namespace App\Http\Controllers;

use App\Http\Requests\HookRequest;
use App\Models\Account;
use App\Models\YClients;
use App\Services\amoCRM;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Ufee\Amo\Oauthapi;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @throws Exception
     */
    public function switch(HookRequest $request)
    {
        $amoApi = amoCRM\Client::install();

        match ($request->post('resource')) {

            'record' => (new RecordController())->index($request, $amoApi),

            'finances_operation' => (new TransactionController())->index($request, $amoApi),
        };
    }
}
