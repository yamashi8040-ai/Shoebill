<?php

namespace App\Tests\Integration\Services;

use App\Services\Helpers\LanguageService;
use App\Tests\Integration\IntegrationTestCase;

class LanguageServiceTest extends IntegrationTestCase
{
    public function test_admin_can_limit_languages_but_english_stays(): void
    {
        $service = app(LanguageService::class);
        $this->assertArrayHasKey('ko', $service->getAvailableLanguages());

        config(['app.languages' => ['ja', 'ko']]);

        $this->assertSame(['en', 'ja', 'ko'], array_values(array_intersect(['en', 'ja', 'ko', 'de'], array_keys($service->getAvailableLanguages()))));
        $this->assertArrayHasKey('de', $service->getAvailableLanguages(includeDisabled: true));
        $this->assertFalse($service->isEnabled('de'));
        $this->assertTrue($service->isEnabled('en'));
    }
}
