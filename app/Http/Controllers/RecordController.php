<?php

namespace App\Http\Controllers;

use App\Http\Requests\HookRequest;
use App\Models\Record;
use App\Models\Client;
use App\Services\amoCRM\Contact;
use App\Services\amoCRM\Lead;
use App\Services\amoCRM\Note;
use Exception;
use Ufee\Amo\Oauthapi;

class RecordController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(HookRequest $request, Oauthapi $amoApi)
    {
        $client = Client::createOrUpdate($request);

        $contact = (new Contact($amoApi))->updateOrCreate($client);

        $record = Record::updateOrCreate($request);

        $lead = (new Lead($amoApi))->updateOrCreate($contact, $record);

        (new Note($amoApi))
            ->create(
                $record->refresh(),
                $lead,
                $record->attedance == 3 ? 'delete' : 'create',
            );
    }
}
