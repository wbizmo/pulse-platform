<?php

namespace App\Domain\Operations;

interface HealthCheck
{
    public function run(): HealthResult;
}
