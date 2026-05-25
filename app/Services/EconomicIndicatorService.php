<?php

namespace App\Services;

class EconomicIndicatorService
{
    /**
     * Calculate Pay Out Time (POT)
     *
     * Find the year when the cumulative Net Cash Flow turns positive.
     */
    public function calculatePot(array $cashFlow): ?float
    {
        $totalYears = count($cashFlow) - 1;

        for ($y = 0; $y < $totalYears; $y++) {
            $currentCum = $cashFlow[$y]['cumulative_ncf'];
            $nextCum = $cashFlow[$y + 1]['cumulative_ncf'];

            if ($currentCum <= 0 && $nextCum > 0) {
                $ncfNext = $cashFlow[$y + 1]['ncf'];
                if ($ncfNext > 0) {
                    $fraction = abs($currentCum) / $ncfNext;
                    return round($y + $fraction, 2);
                }
                return (float) ($y + 1);
            }
        }

        // If cumulative cash flow never becomes positive
        if ($cashFlow[$totalYears]['cumulative_ncf'] <= 0) {
            return null;
        }

        return (float) $totalYears;
    }

    /**
     * Calculate Net Present Value (NPV)
     *
     * Sum of all PV of NCF.
     */
    public function calculateNpv(array $cashFlow): float
    {
        $npv = 0.0;
        foreach ($cashFlow as $row) {
            $npv += $row['pv_ncf'];
        }
        return round($npv, 4);
    }

    /**
     * Helper to calculate NPV for a given rate (decimal)
     */
    private function calculateNpvForRate(array $cashFlow, float $rate): float
    {
        $npv = 0.0;
        foreach ($cashFlow as $row) {
            $year = $row['year'];
            $ncf = $row['ncf'];
            $npv += $ncf / pow(1.0 + $rate, $year);
        }
        return $npv;
    }

    /**
     * Calculate Internal Rate of Return (IRR / ROR)
     *
     * Finds the discount rate where NPV is equal to 0 using Bisection Method.
     * Returns the rate as a percentage (e.g. 15.45)
     */
    public function calculateIrr(array $cashFlow): float
    {
        $low = 0.0;
        $high = 3.0; // Up to 300%
        $tolerance = 1e-6;
        $maxIterations = 150;

        $npvLow = $this->calculateNpvForRate($cashFlow, $low);
        $npvHigh = $this->calculateNpvForRate($cashFlow, $high);

        // If high is still positive, return high
        if ($npvHigh > 0) {
            return $high * 100.0;
        }

        // If low is already negative, return 0
        if ($npvLow < 0) {
            return 0.0;
        }

        for ($i = 0; $i < $maxIterations; $i++) {
            $mid = ($low + $high) / 2.0;
            $npvMid = $this->calculateNpvForRate($cashFlow, $mid);

            if (abs($npvMid) < $tolerance) {
                return round($mid * 100.0, 2);
            }

            if ($npvMid > 0) {
                // We need higher discount rate to reduce NPV to 0
                $low = $mid;
            } else {
                // We need lower discount rate
                $high = $mid;
            }
        }

        return round((($low + $high) / 2.0) * 100.0, 2);
    }

    /**
     * Calculate Profitability Index Ratio (PIR - undiscounted)
     *
     * PIR = Sum(positive NCF) / Sum(Capital + Non-Capital)
     */
    public function calculatePir(array $cashFlow): float
    {
        $positiveNcfSum = 0.0;
        foreach ($cashFlow as $row) {
            if ($row['year'] > 0 && $row['ncf'] > 0) {
                $positiveNcfSum += $row['ncf'];
            }
        }

        // Year 0 has the total investment cost as negative NCF
        $investment = abs($cashFlow[0]['ncf']);

        if ($investment <= 0) {
            return 0.0;
        }

        return round($positiveNcfSum / $investment, 4);
    }

    /**
     * Calculate Discounted Profitability Ratio (DPR)
     *
     * DPR = Sum(positive PV NCF) / Sum(PV Investasi)
     */
    public function calculateDpr(array $cashFlow): float
    {
        $positivePvSum = 0.0;
        foreach ($cashFlow as $row) {
            if ($row['year'] > 0 && $row['pv_ncf'] > 0) {
                $positivePvSum += $row['pv_ncf'];
            }
        }

        $investment = abs($cashFlow[0]['pv_ncf']);

        if ($investment <= 0) {
            return 0.0;
        }

        return round($positivePvSum / $investment, 4);
    }

    /**
     * Generate NPV Sensitivity values at discount rates from 0% to 50%
     *
     * Returns associative array: [rate_percentage => npv_value]
     */
    public function npvSensitivity(array $cashFlow): array
    {
        $sensitivity = [];

        // Steps of 2% from 0% to 50%
        for ($r = 0; $r <= 50; $r += 2) {
            $rateDecimal = $r / 100.0;
            $npv = $this->calculateNpvForRate($cashFlow, $rateDecimal);
            $sensitivity[$r] = round($npv, 2);
        }

        return $sensitivity;
    }

    /**
     * Helper to check project economic feasibility.
     */
    public function checkFeasibility(float $npv, float $irr, float $discountRate): array
    {
        // standard oil & gas feasibility rules:
        // NPV > 0: feasible
        // IRR > discountRate: feasible
        $isFeasible = ($npv > 0) && ($irr > $discountRate);

        return [
            'is_feasible' => $isFeasible,
            'status' => $isFeasible ? 'LAYAK (Feasible)' : 'TIDAK LAYAK (Infeasible)',
            'color' => $isFeasible ? 'emerald' : 'rose'
        ];
    }
}
