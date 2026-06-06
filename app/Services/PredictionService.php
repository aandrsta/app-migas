<?php

namespace App\Services;

class PredictionService
{
    /**
     * Calculate Linear Regression: y = mx + b
     *
     * @param array $x Years (e.g. [1, 2, 3, 4, 5])
     * @param array $y Production values (e.g. [175, 201, 217, 198, ...])
     * @return array ['m' => slope, 'b' => intercept, 'r_squared' => R2]
     */
    public function linearRegression(array $x, array $y): array
    {
        $n = count($x);
        if ($n === 0 || count($y) !== $n) {
            return ['m' => 0.0, 'b' => 0.0, 'r_squared' => 0.0];
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);

        $sumXY = 0.0;
        $sumX2 = 0.0;
        $sumY2 = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if (abs($denominator) < 1e-9) {
            return ['m' => 0.0, 'b' => $n > 0 ? $sumY / $n : 0.0, 'r_squared' => 0.0];
        }

        $m = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $b = ($sumY - ($m * $sumX)) / $n;

        // Calculate R-squared
        $yMean = $sumY / $n;
        $ssTot = 0.0;
        $ssRes = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $yPred = ($m * $x[$i]) + $b;
            $ssRes += ($y[$i] - $yPred) * ($y[$i] - $yPred);
            $ssTot += ($y[$i] - $yMean) * ($y[$i] - $yMean);
        }

        $r2 = $ssTot > 0 ? 1.0 - ($ssRes / $ssTot) : 1.0;

        return [
            'm' => $m,
            'b' => $b,
            'r_squared' => max(0.0, min(1.0, $r2))
        ];
    }

    /**
     * Calculate Quadratic Regression: y = ax^2 + bx + c
     *
     * Using Cramer's rule to solve the 3x3 linear equation system.
     *
     * @param array $x Years
     * @param array $y Production values
     * @return array ['a' => a, 'b' => b, 'c' => c, 'r_squared' => R2]
     */
    public function quadraticRegression(array $x, array $y): array
    {
        $n = count($x);
        if ($n < 3 || count($y) !== $n) {
            // Quadratic requires at least 3 points
            return ['a' => 0.0, 'b' => 0.0, 'c' => 0.0, 'r_squared' => 0.0];
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumX2 = 0.0;
        $sumX3 = 0.0;
        $sumX4 = 0.0;
        $sumXY = 0.0;
        $sumX2Y = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $xi = $x[$i];
            $yi = $y[$i];
            $xi2 = $xi * $xi;
            $xi3 = $xi2 * $xi;
            $xi4 = $xi3 * $xi;

            $sumX2 += $xi2;
            $sumX3 += $xi3;
            $sumX4 += $xi4;
            $sumXY += $xi * $yi;
            $sumX2Y += $xi2 * $yi;
        }

        // We solve the matrix equation: A * C = B
        // Matrix A:
        // [ sumX4, sumX3, sumX2 ]
        // [ sumX3, sumX2, sumX  ]
        // [ sumX2, sumX,  n     ]
        // Matrix B:
        // [ sumX2Y ]
        // [ sumXY  ]
        // [ sumY   ]

        // Calculate Determinant of A (detA)
        $detA = $sumX4 * ($sumX2 * $n - $sumX * $sumX)
              - $sumX3 * ($sumX3 * $n - $sumX2 * $sumX)
              + $sumX2 * ($sumX3 * $sumX - $sumX2 * $sumX2);

        if (abs($detA) < 1e-9) {
            return ['a' => 0.0, 'b' => 0.0, 'c' => $n > 0 ? $sumY / $n : 0.0, 'r_squared' => 0.0];
        }

        // Determinant for a (detA1) - Replace column 1 with B
        $detA1 = $sumX2Y * ($sumX2 * $n - $sumX * $sumX)
               - $sumX3  * ($sumXY * $n - $sumY * $sumX)
               + $sumX2  * ($sumXY * $sumX - $sumY * $sumX2);

        // Determinant for b (detA2) - Replace column 2 with B
        $detA2 = $sumX4 * ($sumXY * $n - $sumY * $sumX)
               - $sumX2Y * ($sumX3 * $n - $sumX2 * $sumX)
               + $sumX2  * ($sumX3 * $sumY - $sumX2 * $sumXY);

        // Determinant for c (detA3) - Replace column 3 with B
        $detA3 = $sumX4 * ($sumX2 * $sumY - $sumX * $sumXY)
               - $sumX3 * ($sumX3 * $sumY - $sumX2 * $sumXY)
               + $sumX2Y * ($sumX3 * $sumX - $sumX2 * $sumX2);

        $a = $detA1 / $detA;
        $b = $detA2 / $detA;
        $c = $detA3 / $detA;

        // Calculate R-squared
        $yMean = $sumY / $n;
        $ssTot = 0.0;
        $ssRes = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $yPred = ($a * $x[$i] * $x[$i]) + ($b * $x[$i]) + $c;
            $ssRes += ($y[$i] - $yPred) * ($y[$i] - $yPred);
            $ssTot += ($y[$i] - $yMean) * ($y[$i] - $yMean);
        }

        $r2 = $ssTot > 0 ? 1.0 - ($ssRes / $ssTot) : 1.0;

        return [
            'a' => $a,
            'b' => $b,
            'c' => $c,
            'r_squared' => max(0.0, min(1.0, $r2))
        ];
    }

    /**
     * Perform production predictions based on known data.
     *
     * @param array $knownYears Known years (e.g. [1, 2, 3, 4])
     * @param array $knownProduction Known production values in MBBL
     * @param int $totalYears Total years (known + predicted, e.g. 20 years)
     * @param float|null $declineRate Custom production decline rate in percent (0-100)
     * @return array Predictions and regression parameters
     */
    public function predict(
        array $knownYears,
        array $knownProduction,
        int $totalYears,
        float $declineRate = null,
        int $declineStartYear = null,
        float $totalReserve = null
    ): array {
        $linear = $this->linearRegression($knownYears, $knownProduction);
        $quadratic = $this->quadraticRegression($knownYears, $knownProduction);

        $predictions = [];
        $n = count($knownYears);

        // 1. Fill known years with actual values
        $cumulativeProduction = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $predictions[$knownYears[$i]] = (double) $knownProduction[$i];
            $cumulativeProduction += (double) $knownProduction[$i];
        }

        // 2. Predict future years
        $lastKnownYear = empty($knownYears) ? 0 : max($knownYears);
        $startDeclineFrom = $declineStartYear ?? ($lastKnownYear + 1);

        for ($year = $lastKnownYear + 1; $year <= $totalYears; $year++) {
            if ($totalReserve !== null && $totalReserve > 0 && $cumulativeProduction >= $totalReserve) {
                $val = 0.0;
            } else {
                if ($declineRate !== null && $declineRate > 0 && $year >= $startDeclineFrom) {
                    // Apply exponential or annual production decline rate from previous year
                    $prevVal = $predictions[$year - 1] ?? 0.0;
                    $val = $prevVal * (1.0 - ($declineRate / 100.0));
                } else {
                    // Use linear regression
                    $val = ($linear['m'] * $year) + $linear['b'];
                }

                // Cap if it exceeds remaining reserve
                if ($totalReserve !== null && $totalReserve > 0) {
                    $remaining = $totalReserve - $cumulativeProduction;
                    if ($val > $remaining) {
                        $val = $remaining;
                    }
                }
            }
            $predictions[$year] = max(0.0, round($val, 4));
            $cumulativeProduction += $predictions[$year];
        }

        return [
            'predictions' => $predictions,
            'linear' => $linear,
            'quadratic' => $quadratic
        ];
    }
}
