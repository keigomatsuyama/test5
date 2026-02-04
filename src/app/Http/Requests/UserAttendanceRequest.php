<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UserAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // rules は最低限
    public function rules(): array
    {
        return [
            'clock_in'  => ['nullable'],
            'clock_out' => ['nullable'],
            'remark'    => ['nullable'],
            'breaks' => ['array'],
            'breaks.*.break_start' => ['nullable'],
            'breaks.*.break_end'   => ['nullable'],
        ];
    }
public function withValidator($validator)
{
    $validator->after(function ($validator) {

        /* ========= ① 出勤・退勤 ========= */
        if ($this->clock_in === null || $this->clock_out === null) {
            $validator->errors()->add(
                'clock_in',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        $clockIn  = $this->clock_in ? Carbon::parse($this->clock_in) : null;
        $clockOut = $this->clock_out ? Carbon::parse($this->clock_out) : null;

        if ($clockIn && $clockOut && $clockIn->gte($clockOut)) {
            $validator->errors()->add(
                'clock_in',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        /* ========= ②③ 休憩 ========= */
        foreach ([0, 1] as $i) {

            $startRaw = $this->breaks[$i]['break_start'] ?? '';
            $endRaw   = $this->breaks[$i]['break_end'] ?? '';

            if ($startRaw === '' || $endRaw === '') {
                $validator->errors()->add(
                    "breaks.$i.break_start",
                    '休憩時間が不適切な値です'
                );
                continue;
            }

            $start = Carbon::parse($startRaw);
            $end   = Carbon::parse($endRaw);

            if ($start->lt($clockIn) || $start->gt($clockOut)) {
                $validator->errors()->add(
                    "breaks.$i.break_start",
                    '休憩時間が不適切な値です'
                );
            }

            if ($end->gt($clockOut) || $end->lte($start)) {
                $validator->errors()->add(
                    "breaks.$i.break_end",
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }
        }

        /* ========= ④ 備考 ========= */
        if ($this->remark === null || $this->remark === '') {
            $validator->errors()->add(
                'remark',
                '備考を記入してください'
            );
        }
    });
}
}