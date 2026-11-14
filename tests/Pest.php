<?php

/**
 * Pest configuration, for the Unit suite.
 */

declare(strict_types=1);

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/*
 * Brain Monkey around every test.
 *
 * setUp() defines WordPress' hook functions and arms Patchwork; tearDown()
 * verifies every expectation the test set (a `->once()` that never happened
 * fails here) and removes every mock, so nothing leaks into the next test.
 *
 * The assertion count: Brain Monkey's expectations are Mockery expectations,
 * which PHPUnit does not count. Without adding them, a test whose only check is
 * `Functions\expect(...)->once()` reports as risky, and failOnRisky fails it.
 * Mockery's PHPUnit integration adds them after each test, and verifies them.
 */
uses(MockeryPHPUnitIntegration::class)
    ->beforeEach(function (): void {
        setUp();
    })
    ->afterEach(function (): void {
        tearDown();
    })
    ->in('Unit');
