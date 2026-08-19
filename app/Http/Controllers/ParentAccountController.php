<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentAccountController extends Controller
{
    public function resetTemporaryPassword(Request $request, User $parent): RedirectResponse
    {
        abort_unless($parent->hasAnyRole(UserRole::Parent), 404);
        abort_unless(filled($parent->email), 422, 'This parent account needs an email address before portal credentials can be issued.');

        $password = Str::upper(Str::random(3)).'@'.Str::random(5);

        $parent->update([
            'password' => $password,
            'temp_password_plaintext' => $password,
            'temp_password_generated_at' => now(),
        ]);

        return redirect()
            ->route('admin.parents.index', array_filter([
                'search' => trim((string) $request->input('redirect_search', '')),
            ]))
            ->with('status', 'Temporary parent password generated successfully.')
            ->with('generated_parent_credentials', [
                'name' => $parent->fullName(),
                'email' => $parent->email,
                'password' => $password,
            ]);
    }
}
