<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show()
    {
        $user = user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'mobile_verified' => user()->mobile_verified,
            'email' => $user->email,
            'email_verified' => $user->email_verified,
            'role' => $user->role()->value,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'introduction' => $user->introduction,
            'realname' => $user->realname,
            'is_password_set' => $user->is_password_set,
            'identity_verified' => $user->identity_verified,
        ]);
    }

    /**
     * Update user profile.
     *
     * @throws ValidationException
     */
    public function update(): JsonResponse
    {
        $data = $this->validate(request(), [
            'nickname' => 'nullable|string|max:50',
            'introduction' => 'nullable|string|max:255',
        ]);

        user()->update($data);

        return $this->success();
    }
}
