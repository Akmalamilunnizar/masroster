<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\MasrosterTestSchema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MasrosterTestSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareMasrosterSchema();
        $this->resetMasrosterData();
    }
}
