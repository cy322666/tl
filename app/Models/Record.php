<?php

namespace App\Models;

use App\Services\YClients;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Record extends Model
{
    /*
    *  3 - Запись удалена,
    *  2 - Пользователь подтвердил запись,
    *  1 - Пользователь пришел, услуги оказаны,
    *  0 - Ожидание пользователя,
    * -1 - Пользователь не пришел на визит
    */
    protected $primaryKey = 'record_id';
    protected $guarded  = [];
    protected $fillable = [
        'record_id',
        'company_id',
        'title',
        'cost',
        'staff_id',
        'staff_name',
        'client_id',
        'visit_id',
        'datetime',
        'comment',
        'seance_length',
        'attendance',
        'status',
    ];

    public static function getEvent(int $attendance) :? string
    {
        return match ($attendance) {
           -1 => 'Клиент не пришел',
            0 => 'Клиент записан',
            1 => 'Клиент пришел',
            2 => 'Клиент подтвердил',
            3 => 'Запись удалена',
        };
    }

    public static function getFilial(int $company_id) :? string
    {
        return match ($company_id) {
            28103, 1021063  => 'Москва Тульская',
            119809, 1021067 => 'Ярославль',
            119834, 1021065 => 'Рыбинск',
            1121147, 274576 => 'Москва Покровка',
        };
    }

    public static function getStatusId(int $attendance, int $pipelineId): int
    {
        if ($pipelineId == env('FIRST_PIPELINE')) {

            return match ($attendance) {
               -1 => env('STATUS_CANCEL'),
                0 => env('STATUS_WAIT'),
                1 => env('STATUS_CAME'),
                2 => env('STATUS_CONFIRM'),
                3 => env('STATUS_DELETE'),
            };
        }
        if ($pipelineId == env('SECOND_PIPELINE')) {

            return match ($attendance) {
               -1 => env('STATUS2_CANCEL'),
                0 => env('STATUS2_WAIT'),
                1 => env('STATUS2_CAME'),
                2 => env('STATUS2_CONFIRM'),
                3 => env('STATUS2_DELETE'),
            };
        }
    }

    public static function updateOrCreate(SymfonyRequest $request): Model|Builder
    {
        return Record::query()
            ->updateOrCreate([
                'record_id'  => $request->data['id'],
            ],[
                'company_id' => $request->company_id,
                'title' => self::buildCommentServices($request->data),
                'cost'  => self::sumCostServices($request->data),
                'staff_id'   => $request->data['staff_id'],
                'staff_name' => $request->data['staff']['name'],
                'client_id'  => $request->data['client']['id'],
                'visit_id'   => $request->data['visit_id'],
                'datetime'   => Carbon::parse($request->data['datetime'])->format('Y.m.d H:i:s'),
                'comment'    => $request->data['comment'],
                'seance_length' => $request->data['length'],
                'attendance' => $request->data['attendance'],
                'status'     => 'no_pay',
            ]);
    }

    private static function sumCostServices(array $array): int
    {
        $costSum = 0;

        if(!empty($arrayRequest['services'][0])) {

            foreach ($arrayRequest['services'] as $array) {

                $costSum += $array['cost'];
            }
        }
        return $costSum ?? 0;
    }

    private static function buildCommentServices(array $arrayRequest): string
    {
        $stringServices = '';

        if(!empty($arrayRequest['services'][0])) {

            foreach ($arrayRequest['services'] as $array) {

                $stringServices .= $array['title'].' |';
            }
            $stringServices = trim($stringServices, ' |', );
        }
        return $stringServices ?? '';
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Client', 'client_id', 'client_id');
    }
}
