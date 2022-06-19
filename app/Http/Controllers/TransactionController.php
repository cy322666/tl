<?php

namespace App\Http\Controllers;

use App\Http\Requests\HookRequest;
use App\Models\Transaction;
use App\Services\amoCRM\Note;
use Ufee\Amo\Oauthapi;

class TransactionController extends Controller
{
    public function index(HookRequest $request, Oauthapi $amoApi)
    {
        $transaction = Transaction::createOrUpdate($request);

        $record = $transaction->record;

        if ($record->lead_id) {

            (new Note($amoApi))
                ->create(
                    $record,
                    $amoApi->leads()->find($record->lead_id),
                    'pay',
                );

            $record->status = 'payed';
            $record->save();
        }
    }
}
