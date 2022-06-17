<?php

namespace App\Services\amoCRM;

use App\Models\Client;
use App\Models\Record;
use Exception;
use Illuminate\Support\Facades\Log;
use Ufee\Amo\Oauthapi;
use Ufee\Amo\Models\Contact;
use Ufee\Amo\Models\Lead as LeadModel;

class Lead
{
    private Oauthapi $amoApi;

    public function __construct(Oauthapi $amoApi)
    {
        $this->amoApi = $amoApi;
    }

    /**
     * @throws Exception
     */
    public function updateOrCreate(Contact $contact, Record $record)
    {
        if ($contact->leads) {

            $lead = $this->search($contact);

            if ($lead)
                $pipelineId = $lead->pipeline_id;
            else
                $pipelineId = env('SECOND_PIPELINE');
        } else
            $pipelineId = env('FIRST_PIPELINE');

        $statusId = Record::getStatusId($record->attendance, $pipelineId);

        if (empty($lead)) {

            $lead = $this->createLead($contact, $record, $statusId);
        } else
            $lead = $this->update($record, $lead, $statusId);

        $record->lead_id = $lead->id;
        $record->save();

        return $lead;
    }

    private function search(Contact $contact)
    {
        $leads = $contact->leads;

        $lead = static::getActiveLead($leads, env('SECOND_PIPELINE'));

        if (!$lead) {

            $lead = static::getActiveLead($leads, env('FIRST_PIPELINE'));
        }
        return $lead ?? true;
    }

    private static function getActiveLead($leads, int $pipelineId)
    {
        return array_filter($leads->toArray(), function($lead) use ($pipelineId) {

            if ($lead['status_id'] != 142 && $lead['status_id'] != 143) {

                return $this->amoApi->leads()->find($lead['id']);
            }
        });
    }

    public function update(Record $record, LeadModel $lead, int $statusId)
    {
        try {
            $lead->sale = $record->cost;
            $lead->status_id = $statusId;

            $lead->cf('Салон')->setValue(Record::getFilial($record->company_id));
            $lead->cf('ID записи, Yclients')->setValue($record->record_id);
            $lead->cf('Дата и время записи, YClients')->setValue($record->datetime);
            $lead->save();

            return $lead;

        } catch (Exception $exception) {

            Log::error(__METHOD__.' : '.$exception->getMessage());
        }
    }

    public function createLead(Contact $contact, Record $record, int $status_id = null): LeadModel
    {
        $lead = $contact->createLead();
        $lead->name = 'Запись в YClients';
        $lead->status_id = $status_id;

        $lead->cf('Салон')->setValue(Record::getFilial($record->company_id));
        $lead->cf('ID записи, Yclients')->setValue($record->record_id);
        $lead->cf('Дата и время записи, YClients')->setValue($record->datetime);
        $lead->cf('roistat')->setValue('yclients_'.Record::getFilial($record->company_id));
        $lead->save();

        return $lead;
    }
}
