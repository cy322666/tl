<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Transaction extends Model
{
    protected $fillable = [
        'company_id',
        'amount',
        'client_id',
        'visit_id',
        'record_id',
        'transaction_id',
        'comment',
    ];

    public static function createOrUpdate(SymfonyRequest $request): Model|\Illuminate\Database\Eloquent\Builder
    {
        return Record::query()
            ->updateOrCreate([
                'transaction_id' => $request->data['id'],
            ],[
                'record_id'  => $request->data['record_id'],
                'company_id' => $request->company_id,
                'client_id'  => $request->data['client']['id'],
                'visit_id'   => $request->data['visit_id'],
                'amount'     => $request->data['amount'],
                'comment'    => $request->data['comment'],
            ]);
            // TODO хз надо ли $record->attendance = 1;
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Client', 'client_id', 'client_id');
    }

    public function record(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Record', 'record_id', 'record_id');
    }
}
