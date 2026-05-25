<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\PredictionService;
use App\Services\DepreciationService;
use App\Services\EconomicIndicatorService;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    /**
     * Get data for interactive charts.
     */
    public function getChartData(
        Project $project,
        PredictionService $predictionService,
        DepreciationService $depreciationService,
        EconomicIndicatorService $economicIndicatorService
    ) {
        // Verify ownership
        abort_if($project->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $project->load(['productionData', 'calculations']);

        // 1. Get production curves (actual, predicted, linear regression, quadratic regression)
        $actual = $project->productionData()
            ->where('is_predicted', false)
            ->orderBy('year')
            ->get();

        $predicted = $project->productionData()
            ->where('is_predicted', true)
            ->orderBy('year')
            ->get();

        $knownYears = $actual->pluck('year')->toArray();
        $knownProd = $actual->pluck('production')->toArray();

        $linearReg = $predictionService->linearRegression($knownYears, $knownProd);
        $quadraticReg = $predictionService->quadraticRegression($knownYears, $knownProd);

        $totalYears = $project->known_years + $project->prediction_years;

        $linearCurve = [];
        $quadraticCurve = [];
        $yearsRange = range(1, $totalYears);

        foreach ($yearsRange as $year) {
            $linearCurve[$year] = round(max(0.0, ($linearReg['m'] * $year) + $linearReg['b']), 4);
            $quadraticCurve[$year] = round(max(0.0, ($quadraticReg['a'] * $year * $year) + ($quadraticReg['b'] * $year) + $quadraticReg['c']), 4);
        }

        // 2. Get depreciation comparisons (all methods)
        $costBase = $project->capital_cost + $project->non_capital_cost;
        $productionsArray = $project->productionData()->orderBy('year')->pluck('production', 'year')->toArray();
        
        $depreciationComparison = $depreciationService->calculateAll(
            $costBase,
            $project->depreciation_years,
            $productionsArray,
            $project->total_reserve ?? 0.0
        );

        // 3. Get cash flow (NCF and Cumulative NCF)
        $calculations = $project->calculations()->orderBy('year')->get();
        $ncfData = $calculations->pluck('ncf', 'year')->toArray();
        $cumulativeNcfData = $calculations->pluck('cumulative_ncf', 'year')->toArray();

        // 4. Get NPV Sensitivity (0% to 50% discount rate)
        $calculationsArray = $calculations->toArray();
        $npvSensitivity = $economicIndicatorService->npvSensitivity($calculationsArray);

        return response()->json([
            'years' => $yearsRange,
            'allYearsWithZero' => range(0, $totalYears),
            'production' => [
                'actual' => $actual->pluck('production', 'year')->toArray(),
                'predicted' => $predicted->pluck('production', 'year')->toArray(),
                'linearCurve' => $linearCurve,
                'quadraticCurve' => $quadraticCurve,
            ],
            'depreciation' => [
                'chosen_method' => $project->depreciation_method,
                'comparison' => $depreciationComparison,
            ],
            'cash_flow' => [
                'ncf' => $ncfData,
                'cumulative_ncf' => $cumulativeNcfData,
            ],
            'npv_sensitivity' => $npvSensitivity,
        ]);
    }
}
