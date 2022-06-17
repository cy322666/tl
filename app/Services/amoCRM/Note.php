<?php

namespace App\Services\amoCRM;

use App\Models\Record;
use App\Models\Transaction;
use Ufee\Amo\Models\Lead as LeadModel;
use Ufee\Amo\Oauthapi;

class Note
{
    private Oauthapi $amoApi;

    public function __construct(Oauthapi $amoApi)
    {
        $this->amoApi = $amoApi;
    }

    public function createNoteLeadTransaction(Transaction $transaction, Record $record, LeadModel $lead)
    {
        $note = $lead->createNote();

        $note->text = self::createNoteTextPay($transaction, $record);

        return $note->save();
    }

    private function createNoteTextPay(Transaction $transaction, Record $record) : string
    {
        return implode("\n", [
            ' - Событие : Оплачена запись № '.$record->record_id,
            ' - Филиал : '.Record::getFilial($record->company_id),
            ' - Стоимость : '.$transaction->amount. ' p',
            ' Комментарий : '.$transaction->comment,
        ]);
    }

    public function createNoteLead(Record $record, LeadModel $lead)
    {
        $note = $lead->createNote();

        $note->text = implode("\n", [
            ' - Событие : '.Record::getEvent($record->attendance),
            ' - Филиал : '.Record::getFilial($record->company_id),
            ' - Процедуры : '.$record->title,
            ' - Дата и Время : '.$record->datetime,
            ' - Мастер : '.$record->staff_name,
            ' Комментарий : '.$record->comment,
        ]);

        return $note->save();
    }

    public function createNoteLeadDelete(Record $record, LeadModel $lead)
    {
        $note = $lead->createNote();

        $note->text = 'Запись № '.$record->record_id.' удалена из YClients';

        return $note->save();
    }

    public function create(Record $record, LeadModel $lead, string $action, $transaction = null)
    {
        return match ($action) {
            'create' => $this->createNoteLead($record, $lead),
            'delete' => $this->createNoteLeadDelete($record, $lead),
            'pay'    => $this->createNoteLeadTransaction($transaction, $record, $lead),
        };
    }
}
