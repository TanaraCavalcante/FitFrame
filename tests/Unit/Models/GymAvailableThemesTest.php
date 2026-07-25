<?php

namespace Tests\Unit\Models;

use App\Models\Gym;
use Tests\TestCase;

class GymAvailableThemesTest extends TestCase
{
    public function test_lists_theme_names_excluding_base(): void
    {
        $themes = Gym::availableThemes();

        $this->assertContains('pulse', $themes);
        $this->assertContains('iron-house', $themes);
        $this->assertContains('zenflow', $themes);
        $this->assertNotContains('base', $themes);
    }
}
