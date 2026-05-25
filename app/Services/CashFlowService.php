<?php

namespace App\Services;

use App\Models\Project;

class CashFlowService
{
    protected $depreciationService;

    public function __construct(DepreciationService $depreciationService)
    {
        $this->depreciationService = $depreciationService;
    }

    /**
     * Calculate year-by-year cash flow details for a project.
     *
     * @param Project $project The project model
     * @param array $productions Production per year (keyed by year index: 1, 2, ...)
     * @return array Array of cash flow rows for each year (from 0 to totalYears)
     */
    public function calculateCashFlow(Project $project, array $productions): array
    {
        $totalYears = $project->known_years + $project->prediction_years;
        $depreciationYears = $project->depreciation_years;
        $depreciationMethod = $project->depreciation_method;

        // Cost base for depreciation is Capital + Non-Capital (as per excel example)
        $costBase = $project->capital_cost + $project->non_capital_cost;

        // 1. Calculate depreciation
        $depreciationArray = $this->depreciationService->calculate(
            $depreciationMethod,
            $costBase,
            $depreciationYears,
            $productions,
            $project->total_reserve ?? 0.0,
            $project->custom_depreciation_rate
        );

        $cashFlow = [];
        $runningCumulativeNcf = 0.0;

        $taxRate = $project->tax_rate > 1 ? $project->tax_rate / 100 : $project->tax_rate;
        $discountRate = $project->discount_rate > 1 ? $project->discount_rate / 100 : $project->discount_rate;

        // --- Year 0 (Investment Year) ---
        $cap0 = $project->capital_cost;
        $nonCap0 = $project->non_capital_cost;
        $ncf0 = -($cap0 + $nonCap0);
        $runningCumulativeNcf += $ncf0;

        $cashFlow[0] = [
            'year' => 0,
            'production' => 0.0,
            'income' => 0.0,
            'capital' => $cap0,
            'non_capital' => $nonCap0,
            'opex' => 0.0,
            'depreciation' => 0.0,
            'taxable_income' => 0.0,
            'tax' => 0.0,
            'ncf' => $ncf0,
            'cumulative_ncf' => $runningCumulativeNcf,
            'discount_factor' => 1.0,
            'pv_ncf' => $ncf0,
        ];

        // --- Year 1 to N ---
        for ($year = 1; $year <= $totalYears; $year++) {
            $prod = $productions[$year] ?? 0.0;
            $income = $prod * $project->oil_price;
            $opex = $project->opex_per_year;
            $dep = $depreciationArray[$year] ?? 0.0;

            // Taxable Income = Income - OPEX - Depreciation
            $taxableIncome = $income - $opex - $dep;

            // Tax = Tax Rate * max(Taxable Income, 0)
            $tax = $taxableIncome > 0 ? $taxableIncome * $taxRate : 0.0;

            // NCF = Taxable Income - Tax (which matches the contoh perhitungan excel H - I)
            $ncf = $taxableIncome - $tax;

            $runningCumulativeNcf += $ncf;

            // Discount Factor = 1 / (1 + r)^year
            $discountFactor = 1.0 / pow(1.0 + $discountRate, $year);
            $pvNcf = $ncf * $discountFactor;

            $cashFlow[$year] = [
                'year' => $year,
                'production' => round($prod, 4),
                'income' => round($income, 4),
                'capital' => 0.0,
                'non_capital' => 0.0,
                'opex' => round($opex, 4),
                'depreciation' => round($dep, 4),
                'taxable_income' => round($taxableIncome, 4),
                'tax' => round($tax, 4),
                'ncf' => round($ncf, 4),
                'cumulative_ncf' => round($runningCumulativeNcf, 4),
                'discount_factor' => round($discountFactor, 6),
                'pv_ncf' => round($pvNcf, 4),
            ];
        }

        return $cashFlow;
    }
}
