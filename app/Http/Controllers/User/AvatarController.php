<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    /**
     * 上传并更新用户头像
     */
    public function update(Request $request): JsonResponse
    {
        $user = user();
        $data = $this->validate($request, [
            'avatar' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);
        // 删除旧文件（若存在）
        if (! empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return $this->success([
            'avatar' => $user->avatar,
            'avatar_url' => Storage::disk('public')->url($user->avatar),
        ]);
    }

    /**
     * 删除用户头像（恢复默认）
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = user();
        if (! empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = null;
        $user->save();

        return $this->success(['avatar' => null, 'avatar_url' => null]);
    }
}
