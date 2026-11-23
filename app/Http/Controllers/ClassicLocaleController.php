<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

/**
 * Translation strings for the Classic UI's i18next loader, served from Shoebill's own lang/ files.
 * Adapted from Pterodactyl's LocaleController (MIT, © Dane Everitt and contributors).
 */
class ClassicLocaleController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'regex:/^[a-z]{2,3}(_[A-Za-z]{2,4})?$/'],
            'namespace' => ['required', 'string', 'regex:/^[a-z_-]+$/'],
        ]);

        $strings = Lang::getLoader()->load($data['locale'], $data['namespace']);
        $response = [$data['locale'] => [$data['namespace'] => $this->i18n($strings)]];

        return new JsonResponse($response, 200, ['Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400']);
    }

    /** Laravel ":foo" placeholders become i18next "{{foo}}". */
    private function i18n(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = is_array($value) ? $this->i18n($value) : preg_replace('/:([\w.-]+\w)([^\w:]?|$)/m', '{{$1}}$2', $value);
        }

        return $data;
    }
}
