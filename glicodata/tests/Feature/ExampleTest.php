<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_to_ubs_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('ubs.login'));
    }
}
