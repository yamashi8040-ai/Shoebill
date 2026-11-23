<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Serves the Pterodactyl React client UI ("Classic UI") from public/classic-assets against Shoebill's client API. */
class ClassicUiController extends Controller
{
    public function __invoke(Request $request): View
    {
        $manifest = public_path('classic-assets/manifest.json');
        abort_unless(is_file($manifest), 503, 'The Classic UI is not built. Run: cd classic && yarn install && yarn build:production');

        $user = $request->user();

        return view('classic', [
            'bundle' => json_decode(file_get_contents($manifest), true)['main.js'],
            'user' => [
                'uuid' => $user->uuid,
                'username' => $user->username,
                'email' => $user->email,
                'language' => $user->language,
                'root_admin' => $user->isAdmin(),
                'use_totp' => !empty($user->mfa_app_secret),
                'created_at' => $user->created_at?->toAtomString(),
                'updated_at' => $user->updated_at?->toAtomString(),
            ],
            'site' => [
                'name' => config('app.name'),
                'locale' => $user->language ?? config('app.locale'),
                'recaptcha' => ['enabled' => false, 'siteKey' => ''],
            ],
        ]);
    }
}
