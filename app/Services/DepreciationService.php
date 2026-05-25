<?php

namespace App\Services;

class DepreciationService
{
    /**
     * Straight Line Method: Di = Cost / N
     */
    public function straightLine(float $cost, int $years): array
    {
        $depreciation = [];
        if ($years <= 0) return [];

        $di = $cost / $years;
        for ($i = 1; $i <= $years; $i++) {
            $depreciation[$i] = round($di, 4);
        }

        return $depreciation;
    }

    /**
     * Declining Balance Method: Di = Cost * R * (1-R)^(i-1), where R = 1/N
     */
    public function decliningBalance(float $cost, int $years): array
    {
        $depreciation = [];
        if ($years <= 0) return [];

        $rate = 1.0 / $years;
        $bookValue = $cost;

        for ($i = 1; $i <= $years; $i++) {
            if ($i === $years) {
                // In the last year, write off the remaining book value
                $di = $bookValue;
            } else {
                $di = $cost * $rate * pow(1.0 - $rate, $i - 1);
                $bookValue -= $di;
            }
            $depreciation[$i] = round($di, 4);
        }

        return $depreciation;
    }

    /**
     * Double Declining Balance Method: Di = BookValue * (2/N)
     */
    public function doubleDeclining(float $cost, int $years): array
    {
        $depreciation = [];
        if ($years <= 0) return [];

        $rate = 2.0 / $years;
        $bookValue = $cost;

        for ($i = 1; $i <= $years; $i++) {
            if ($i === $years) {
                // In the last year, write off the remaining book value to reach 0
                $di = $bookValue;
            } else {
                $di = $bookValue * $rate;
                // Avoid depreciating more than cost
                if ($di > $bookValue) {
                    $di = $bookValue;
                }
                $bookValue -= $di;
            }
            $depreciation[$i] = round($di, 4);
        }

        return $depreciation;
    }

    /**
     * Unit of Production Method: Di = (Qi / Reserve) * Cost
     *
     * @param double $cost Total cost base
     * @param array $productions Production per year (keyed by year index: 1, 2, ...)
     * @param double $reserve Total oil/gas reserve
     * @return array Depreciation per year
     */
    public function unitOfProduction(float $cost, array $productions, float $reserve): array
    {
        $depreciation = [];
        if ($reserve <= 0) {
            foreach ($productions as $year => $prod) {
                $depreciation[$year] = 0.0;
            }
            return $depreciation;
        }

        foreach ($productions as $year => $prod) {
            $di = ($prod / $reserve) * $cost;
            $depreciation[$year] = round($di, 4);
        }

        return $depreciation;
    }

    /**
     * Sum of the Years Digits Method: Di = ((N - i + 1) / SumOfDigits) * Cost
     */
    public function sumOfYear(float $cost, int $years): array
    {
        $depreciation = [];
        if ($years <= 0) return [];

        $sumOfDigits = ($years * ($years + 1)) / 2;

        for ($i = 1; $i <= $years; $i++) {
            $di = (($years - $i + 1) / $sumOfDigits) * $cost;
            $depreciation[$i] = round($di, 4);
        }

        return $depreciation;
    }

    /**
     * Dispatcher method to calculate depreciation based on chosen method.
     */
    public function calculate(string $method, float $cost, int $years, array $productions = [], float $reserve = 0.0): array
    {
        switch ($method) {
            case 'straight_line':
                return $this->straightLine($cost, $years);
            case 'declining_balance':
                return $this->decliningBalance($cost, $years);
            case 'double_declining':
                return $this->doubleDeclining($cost, $years);
            case 'unit_of_production':
                return $this->unitOfProduction($cost, $productions, $reserve);
            case 'sum_of_year':
                return $this->sumOfYear($cost, $years);
            default:
                return $this->straightLine($cost, $years);
        }
    }

    /**
     * Calculate all methods to provide comparison data (for charting in Phase 5).
     */
    public function calculateAll(float $cost, int $years, array $productions = [], float $reserve = 0.0): array
    {
        return [
            'straight_line' => $this->straightLine($cost, $years),
            'declining_balance' => $this->decliningBalance($cost, $years),
            'double_declining' => $this->doubleDeclining($cost, $years),
            'unit_of_production' => $this->unitOfProduction($cost, $productions, $reserve),
            'sum_of_year' => $this->sumOfYear($cost, $years),
        ];
    }
}
