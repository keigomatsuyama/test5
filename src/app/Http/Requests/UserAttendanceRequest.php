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
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'remark'    => ['required', 'string'], // ← 必須にする
            'breaks'    => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end'   => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'remark.required' => '備考を記入してください',
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_in.date_format' => '出勤時間はHH:MM形式で入力してください',
            'clock_out.date_format' => '退勤時間はHH:MM形式で入力してください',

            'breaks.*.break_start.date_format' => '休憩時間はHH:MM形式で入力してください',
            'breaks.*.break_end.date_format' => '休憩時間はHH:MM形式で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // フォーマットエラーがある場合は Carbon にしない
            if (
                $validator->errors()->has('clock_in') ||
                $validator->errors()->has('clock_out')
            ) {
                return;
            }

            $clockIn  = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            // 出勤・退勤チェック
            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add(
                    'clock_out',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 休憩チェック
            foreach ($this->breaks ?? [] as $index => $break) {

                if (empty($break['break_start']) || empty($break['break_end'])) {
                    continue;
                }

                $startTime = Carbon::createFromFormat('H:i', $break['break_start']);
                $endTime   = Carbon::createFromFormat('H:i', $break['break_end']);

                if ($startTime->gte($endTime)) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                if ($startTime->lt($clockIn)) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($endTime->gt($clockOut)) {
                    $validator->errors()->add(
                        "breaks.$index.break_end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
