<?php

namespace App\Services\Admin;

use App\Models\Lawyer;
use App\Models\Representative;
use App\Models\User;
use Auth;
use Exception;
use Hash;
use Log;

class AdminService
{
    public function store(array $data)
    {
        if (strpos($data['email'], '@admin') === false) {
            return [
                'status' => false,
                'msg' => 'Email address must contains mark @admin',
                'code' => 400
            ];
        }

        try {
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->save();

            $user->role()->create([
                'name' => 'admin'
            ]);
            Log::info($user);
            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new employee. Please try again later.', 'code' => 500];
        }
    }

    public function signupEmployee(array $data)
    {
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized.',
                'code' => 422
            ];
        }

        try {
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->save();

            $user->role()->create([
                'name' => 'employee'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new employee. Please try again later.', 'code' => 500];
        }
    }

    public function signupLawyer(array $data)
    {
        try {
            $lawyer = Lawyer::create($data);
            $lawyer->password = Hash::make($data["password"]);
            $lawyer->save();

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new lawyer. Please try again later.', 'code' => 500];
        }
    }

    public function signupRepresentative(array $data)
    {
        try {
            $representative = Representative::create($data);
            $representative->password = Hash::make($data["password"]);
            $representative->save();

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new representative. Please try again later.', 'code' => 500];
        }
    }

    public function signupUser(array $data)
    {
        if (Auth::user()->role->name !== 'admin') {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized.',
                'code' => 422
            ];
        }

        try {
            $user = User::create($data);
            $user->password = Hash::make($data["password"]);
            $user->save();

            $user->role()->create([
                'name' => 'user'
            ]);

            // Mail::to($user->email)->send(new VerifyCodeMail($user));

            return ['status' => true];
        } catch (Exception $e) {
            return ['status' => false, 'msg' => 'Unable to create new user. Please try again later.', 'code' => 500];
        }
    }
}
