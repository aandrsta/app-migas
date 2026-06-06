<?php

namespace Tests\Unit;

use App\Services\PredictionService;
use PHPUnit\Framework\TestCase;

class PredictionServiceTest extends TestCase
{
    protected $predictionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->predictionService = new PredictionService();
    }

    /**
     * Test that prediction correctly starts decline at a specific year,
     * and uses linear regression before that start year.
     */
    public function test_decline_starts_at_custom_year(): void
    {
        $knownYears = [1, 2, 3];
        $knownProduction = [100, 100, 100]; // Linear regression will predict 100 for any year
        $totalYears = 5;
        $declineRate = 10.0; // 10%
        $declineStartYear = 5; // Decline only starts at year 5

        $result = $this->predictionService->predict(
            $knownYears,
            $knownProduction,
            $totalYears,
            $declineRate,
            $declineStartYear
        );

        $predictions = $result['predictions'];

        // Year 4 is before decline start year, so it should be predicted using linear regression (which is 100)
        $this->assertEquals(100.0, $predictions[4]);

        // Year 5 is the decline start year, so it should decline by 10% from Year 4's value (100 -> 90)
        $this->assertEquals(90.0, $predictions[5]);
    }

    /**
     * Test that cumulative production (known + predicted) is capped by the total reserve,
     * and drops to 0 once the reserve is fully depleted.
     */
    public function test_production_is_capped_by_total_reserve(): void
    {
        $knownYears = [1, 2];
        $knownProduction = [100, 50]; // Total actual is 150
        $totalYears = 4;
        $declineRate = null; // Use linear regression
        $declineStartYear = null;
        $totalReserve = 180.0; // Reserves only allow 30.0 more production

        $result = $this->predictionService->predict(
            $knownYears,
            $knownProduction,
            $totalYears,
            $declineRate,
            $declineStartYear,
            $totalReserve
        );

        $predictions = $result['predictions'];

        // Sum of years 1 and 2 is 150.
        // Linear regression: Year 3 = 0, Year 4 = 0.
        $this->assertEquals(100.0, $predictions[1]);
        $this->assertEquals(50.0, $predictions[2]);
        $this->assertEquals(0.0, $predictions[3]);
        $this->assertEquals(0.0, $predictions[4]);
    }

    /**
     * Test reserve capping with decline rate.
     */
    public function test_production_is_capped_by_total_reserve_with_decline(): void
    {
        $knownYears = [1];
        $knownProduction = [100]; // Actual: 100
        $totalYears = 3;
        $declineRate = 10.0; // Year 2 prediction: 90
        $declineStartYear = null;
        $totalReserve = 150.0; // Reserve left after Year 1: 50. So Year 2 is capped at 50, Year 3 is 0.

        $result = $this->predictionService->predict(
            $knownYears,
            $knownProduction,
            $totalYears,
            $declineRate,
            $declineStartYear,
            $totalReserve
        );

        $predictions = $result['predictions'];

        $this->assertEquals(100.0, $predictions[1]);
        // Year 2 should be capped at 50.0 (since 100 + 50 = 150)
        $this->assertEquals(50.0, $predictions[2]);
        // Year 3 should be 0.0 (reserves exhausted)
        $this->assertEquals(0.0, $predictions[3]);
    }
}
