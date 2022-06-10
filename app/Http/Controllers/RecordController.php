<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    private $array_status = [];

    /**
     * @param Request $request
     *
     * получаем модели клиента и записи
     * вызываем экшен события
     */
    public function index(Request $request)
    {
        $requestArray = $request::capture()->post();

        $client = Client::getClient($requestArray);
        $record = Record::getRecord($requestArray);

        if($record == null || $client == null) exit;

        //TODO если attendance равен тому шо в бд, то это обновление

        if($requestArray['status'] == 'delete') {

            $record->attendance = -1;
            $status = 3;

        } else {
            $status = $requestArray['data']['attendance'];
            $record->attendance = $requestArray['data']['attendance'];
        }

//        Log::info("status у записи # ".$record->id." : ".$status);
//        Log::info("attendance у записи # ".$record->id." : ".$record->attendance);

        $this->array_status = Record::getStatus($status);

        $action = $this->array_status['action'];

//        Log::info("action у записи # ".$record->id." : ".$action);

        $record->save();

        $this->$action($client, $record->refresh());
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Пришел по записи
     */
    public function came(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;
        $client->save();

//        Log::info("сущность contact : ".$contact->id);

        $lead = $this->amoApi->searchOrCreate($client, $record->refresh());

        $record->lead_id = $lead->id;
        $record->save();

        $this->amoApi->updateLead($record->refresh());

        $this->amoApi->createNoteLead($record->refresh());
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * клиент записан
     */
    public function wait(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;
        $client->save();

//        Log::info("сущность contact : ".$contact->id);

        $lead = $this->amoApi->searchOrCreate($client, $record->refresh());

        $record->lead_id = $lead->id;
        $record->save();

//        Log::info("сущность lead : ".$lead->id);

        $this->amoApi->updateLead($record->refresh());

        $this->amoApi->createNoteLead($record->refresh());
    }

    public function confirm(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;
        $client->save();

        $lead = $this->amoApi->searchOrCreate($client, $record->refresh());

        $record->lead_id = $lead->id;
        $record->save();

        $this->amoApi->updateLead($record->refresh());

        $this->amoApi->createNoteLead($record->refresh());
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Запись отменена
     */
    public function cancel(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;
        $client->save();

//        Log::info("сущность contact : ".$contact->id);

        $lead = $this->amoApi->searchOrCreate($client, $record->refresh());

        $record->lead_id = intval($lead->id);
        $record->save();

//        Log::info("сущность lead : ".$lead->id);

        $this->amoApi->updateLead($record->refresh());

        $this->amoApi->createNoteLead($record->refresh());
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Запись удалена
     */
    public function delete(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        if($record->lead_id) {

//            Log::info("сущность lead : ".$record->lead_id);

            $lead = $this->amoApi->getLead($record->lead_id);

            $status_id = $this->amoApi::pipelineHelper($lead->pipeline_id, $record->refresh());

            $this->amoApi->updateStatus($lead, $status_id);

            $record->save();

            $this->amoApi->createNoteLeadDelete($record->refresh());
        }
    }
}
