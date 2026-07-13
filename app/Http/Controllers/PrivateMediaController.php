<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaController extends Controller
{
    public function avatar(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();
        abort_unless($viewer && $this->mayViewAvatar($viewer, $user), 403);
        abort_unless($user->avatar_path && Storage::disk('local')->exists($user->avatar_path), 404);

        return Storage::disk('local')->response(
            $user->avatar_path,
            null,
            [
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    protected function mayViewAvatar(User $viewer, User $subject): bool
    {
        if ($viewer->is($subject)) {
            return true;
        }

        if ($viewer->hasAnyRole(UserRole::Admin, UserRole::Principal)) {
            return true;
        }

        if ($viewer->hasAnyRole(UserRole::Parent)) {
            return (int) ($subject->studentProfile?->parent_user_id ?? 0) === (int) $viewer->id;
        }

        return false;
    }
}
