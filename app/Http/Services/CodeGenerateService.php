<?php

namespace App\Http\Services;

use App\Mail\CodeMail;
use App\Models\CodeGenerate;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class CodeGenerateService
{
    /**
     * Generate code
     * @param array $data
     * @return array
     */
    public function generate(array $data)
    {
        $random_code = rand(100000, 999999);
        $expired = Carbon::now()->addMinutes(5);

        $email = $this->checkEmail($data['email']);
        if (!$email) {
            return [
                'status' => false,
                'msg' => 'This Email is Exists Already!',
                'code' => 403
            ];
        }

        try {
            $code = CodeGenerate::firstOrCreate([
                'email' => $data['email'],
            ]);

            if ($code->wasRecentlyCreated) {
                $code->code = $random_code;
                $code->expiration_date = $expired;
                $code->save();
                Mail::to($data['email'])->send(new CodeMail($data['email'], $random_code));
                return ['status' => true];
            } else {
                $expiration_date = Carbon::parse($code->expiration_date);
                if (!$expiration_date->isFuture()) {
                    $random_code = rand(100000, 999999);
                    $expired = Carbon::now()->addMinutes(5);

                    $code->code = $random_code;
                    $code->expiration_date = $expired;
                    $code->save();
                    Mail::to($data['email'])->send(new CodeMail($data['email'], $random_code));
                    return ['status' => true];
                } else {
                    return [
                        'status' => false,
                        'msg' => 'Send Code Already!',
                        'code' => 403
                    ];
                }
            }
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Verify code
     * @param array $data
     * @return array
     */
    public function checkCode(array $data)
    {
        $code = CodeGenerate::where('email', $data['email'])->where('code', $data['code'])->first();
        if (!$code) {
            return [
                'status' => false,
                'msg' => 'Code In-valid!',
                'code' => 403
            ];
        }

        $expired_date = Carbon::parse($code->expiration_date);
        if (!$expired_date->isFuture()) {
            return [
                'status' => false,
                'msg' => 'Code is Expired Date!',
                'code' => 403
            ];
        }

        $code->expiration_date = Carbon::now();
        $code->is_verify = true;
        $code->save();
        return ['status' => true];
    }

    /**
     * Check if email is exists
     * @param string $email
     * @return bool|string
     */
    public function checkEmail(string $email)
    {
        return (User::where('email', $email)->exists()
            || Lawyer::where('email', $email)->exists()
            || Representative::where('email', $email)->exists()) ? false : $email;
    }
}
