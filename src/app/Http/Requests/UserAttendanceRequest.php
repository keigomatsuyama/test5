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

    public function rules(): array
    {
        return [
            'clock_in'  => ['nullable', ],
            'clock_out' => ['nullable', ],
            'remark'    => ['nullable'],
            'breaks' => ['array'],
            'breaks.*.break_start' => ['nullable', ],
            'breaks.*.break_end'   => ['nullable', ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = null;
            $clockOut = null;

            /* ===== 出勤・退勤 ===== */

            if (!$this->clock_in || !$this->clock_out) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            } else {

                $clockIn  = Carbon::createFromFormat('H:i', $this->clock_in);
                $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

                if ($clockIn->gte($clockOut)) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }
            }

            /* ===== 休憩 ===== */

            foreach ($this->breaks ?? [] as $i => $break) {

                $start = $break['break_start'] ?? null;
                $end   = $break['break_end'] ?? null;

                // 両方空なら無視
                if (empty($start) && empty($end)) {
                    continue;
                }

                // 片方だけ入力
                if (empty($start) || empty($end)) {
                    $validator->errors()->add(
                        "breaks.$i.break_start",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                $startTime = Carbon::createFromFormat('H:i', $start);
                $endTime   = Carbon::createFromFormat('H:i', $end);

                // 開始 >= 終了
                if ($startTime->gte($endTime)) {
                    $validator->errors()->add(
                        "breaks.$i.break_end",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                // 出勤退勤が正常な場合のみ範囲チェック
                if ($clockIn && $clockOut) {

                    // ② 休憩開始が勤務時間外
                    if ($startTime->lt($clockIn) || $startTime->gt($clockOut)) {
                        $validator->errors()->add(
                            "breaks.$i.break_start",
                            '休憩時間が不適切な値です'
                        );
                    }
                    // ③ 休憩終了が退勤より後
                    if ($endTime->gt($clockOut)) {
                        $validator->errors()->add(
                            "breaks.$i.break_end",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }

            /* ===== 備考 ===== */

            if (!$this->remark || trim($this->remark) === '') {
                $validator->errors()->add(
                    'remark',
                    '備考を記入してください'
                );
            }
        });
    }
}