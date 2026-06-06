<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\PredictionService;
use App\Services\DepreciationService;
use App\Services\CashFlowService;
use App\Services\EconomicIndicatorService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $predictionService;
    protected $depreciationService;
    protected $cashFlowService;
    protected $economicIndicatorService;

    public function __construct(
        PredictionService $predictionService,
        DepreciationService $depreciationService,
        CashFlowService $cashFlowService,
        EconomicIndicatorService $economicIndicatorService
    ) {
        $this->predictionService = $predictionService;
        $this->depreciationService = $depreciationService;
        $this->cashFlowService = $cashFlowService;
        $this->economicIndicatorService = $economicIndicatorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = auth()->user()->projects()
            ->withSum('calculations as total_ncf', 'ncf')
            ->withSum('calculations as npv', 'pv_ncf')
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'oil_price' => 'required|numeric|min:0',
            'capital_cost' => 'required|numeric|min:0',
            'non_capital_cost' => 'required|numeric|min:0',
            'opex_per_year' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'known_years' => 'required|integer|min:1',
            'prediction_years' => 'required|integer|min:1',
            'depreciation_years' => 'required|integer|min:1',
            'depreciation_method' => 'required|string|in:straight_line,declining_balance,double_declining,unit_of_production,sum_of_year',
            'discount_rate' => 'required|numeric|min:0|max:100',
            'total_reserve' => 'nullable|numeric|min:0',
            'decline_rate' => 'nullable|numeric|min:0|max:100',
            'custom_depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'decline_start_year' => 'nullable|integer|min:1',
            'production' => 'required|array',
            'production.*' => 'required|numeric|min:0',
        ];

        // Conditional validation: total_reserve is required if depreciation_method is unit_of_production
        if ($request->input('depreciation_method') === 'unit_of_production') {
            $rules['total_reserve'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Custom validation: total_reserve must not be less than total actual production entered
        $totalActualProduction = array_sum($request->input('production', []));
        if (!empty($validated['total_reserve']) && $validated['total_reserve'] < $totalActualProduction) {
            return back()->withErrors(['total_reserve' => 'Total cadangan minyak tidak boleh kurang dari total produksi aktual yang dimasukkan (yaitu ' . number_format($totalActualProduction, 2) . ' MBBL).'])->withInput();
        }

        // 1. Create the project
        $project = auth()->user()->projects()->create([
            'name' => $validated['name'],
            'oil_price' => $validated['oil_price'],
            'capital_cost' => $validated['capital_cost'],
            'non_capital_cost' => $validated['non_capital_cost'],
            'opex_per_year' => $validated['opex_per_year'],
            'tax_rate' => $validated['tax_rate'],
            'known_years' => $validated['known_years'],
            'prediction_years' => $validated['prediction_years'],
            'depreciation_years' => $validated['depreciation_years'],
            'depreciation_method' => $validated['depreciation_method'],
            'discount_rate' => $validated['discount_rate'],
            'total_reserve' => $validated['total_reserve'] ?? null,
            'decline_rate' => $validated['decline_rate'] ?? null,
            'custom_depreciation_rate' => $validated['custom_depreciation_rate'] ?? null,
            'decline_start_year' => $validated['decline_start_year'] ?? null,
        ]);

        // 2. Save known actual production data
        $productionRecords = [];
        foreach ($validated['production'] as $year => $value) {
            $productionRecords[] = [
                'project_id' => $project->id,
                'year' => $year,
                'production' => $value,
                'is_predicted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $project->productionData()->insert($productionRecords);

        // 3. Run all calculations (predict future production + cash flows)
        $this->runCalculations($project);

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil dibuat dan dihitung!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // Ownership check
        abort_if($project->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $project->load(['productionData', 'calculations']);

        // 1. Separate actual and predicted production (in-memory)
        $actualProduction = $project->productionData
            ->where('is_predicted', false)
            ->sortBy('year')
            ->values();

        $predictedProduction = $project->productionData
            ->where('is_predicted', true)
            ->sortBy('year')
            ->values();

        // 2. Run regression parameters on actual production for display in Production Tab info box
        $knownYears = $actualProduction->pluck('year')->toArray();
        $knownProd = $actualProduction->pluck('production')->toArray();
        
        $linearRegression = $this->predictionService->linearRegression($knownYears, $knownProd);
        $quadraticRegression = $this->predictionService->quadraticRegression($knownYears, $knownProd);

        // 3. Get depreciation comparison for chart
        $costBase = $project->capital_cost + $project->non_capital_cost;
        $productionsArray = $project->productionData->sortBy('year')->pluck('production', 'year')->toArray();
        
        $depreciationComparison = $this->depreciationService->calculateAll(
            $costBase,
            $project->depreciation_years,
            $productionsArray,
            $project->total_reserve ?? 0.0,
            $project->custom_depreciation_rate
        );

        // 4. Calculate economic indicators
        $calculations = $project->calculations->sortBy('year')->toArray();
        
        $npv = $this->economicIndicatorService->calculateNpv($calculations);
        $irr = $this->economicIndicatorService->calculateIrr($calculations);
        $pot = $this->economicIndicatorService->calculatePot($calculations);
        $pir = $this->economicIndicatorService->calculatePir($calculations);
        $dpr = $this->economicIndicatorService->calculateDpr($calculations);
        
        $feasibility = $this->economicIndicatorService->checkFeasibility($npv, $irr, $project->discount_rate);

        return view('projects.show', compact(
            'project',
            'actualProduction',
            'predictedProduction',
            'linearRegression',
            'quadraticRegression',
            'depreciationComparison',
            'npv',
            'irr',
            'pot',
            'pir',
            'dpr',
            'feasibility'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        abort_if($project->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $actualProduction = $project->productionData()
            ->where('is_predicted', false)
            ->orderBy('year')
            ->get();

        return view('projects.edit', compact('project', 'actualProduction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        abort_if($project->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $rules = [
            'name' => 'required|string|max:255',
            'oil_price' => 'required|numeric|min:0',
            'capital_cost' => 'required|numeric|min:0',
            'non_capital_cost' => 'required|numeric|min:0',
            'opex_per_year' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'known_years' => 'required|integer|min:1',
            'prediction_years' => 'required|integer|min:1',
            'depreciation_years' => 'required|integer|min:1',
            'depreciation_method' => 'required|string|in:straight_line,declining_balance,double_declining,unit_of_production,sum_of_year',
            'discount_rate' => 'required|numeric|min:0|max:100',
            'total_reserve' => 'nullable|numeric|min:0',
            'decline_rate' => 'nullable|numeric|min:0|max:100',
            'custom_depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'decline_start_year' => 'nullable|integer|min:1',
            'production' => 'required|array',
            'production.*' => 'required|numeric|min:0',
        ];

        if ($request->input('depreciation_method') === 'unit_of_production') {
            $rules['total_reserve'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Custom validation: total_reserve must not be less than total actual production entered
        $totalActualProduction = array_sum($request->input('production', []));
        if (!empty($validated['total_reserve']) && $validated['total_reserve'] < $totalActualProduction) {
            return back()->withErrors(['total_reserve' => 'Total cadangan minyak tidak boleh kurang dari total produksi aktual yang dimasukkan (yaitu ' . number_format($totalActualProduction, 2) . ' MBBL).'])->withInput();
        }

        // 1. Update project configuration
        $project->update([
            'name' => $validated['name'],
            'oil_price' => $validated['oil_price'],
            'capital_cost' => $validated['capital_cost'],
            'non_capital_cost' => $validated['non_capital_cost'],
            'opex_per_year' => $validated['opex_per_year'],
            'tax_rate' => $validated['tax_rate'],
            'known_years' => $validated['known_years'],
            'prediction_years' => $validated['prediction_years'],
            'depreciation_years' => $validated['depreciation_years'],
            'depreciation_method' => $validated['depreciation_method'],
            'discount_rate' => $validated['discount_rate'],
            'total_reserve' => $validated['total_reserve'] ?? null,
            'decline_rate' => $validated['decline_rate'] ?? null,
            'custom_depreciation_rate' => $validated['custom_depreciation_rate'] ?? null,
            'decline_start_year' => $validated['decline_start_year'] ?? null,
        ]);

        // 2. Replace production data: delete all old production data first
        $project->productionData()->delete();

        // 3. Save new actual production data
        $productionRecords = [];
        foreach ($validated['production'] as $year => $value) {
            $productionRecords[] = [
                'project_id' => $project->id,
                'year' => $year,
                'production' => $value,
                'is_predicted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $project->productionData()->insert($productionRecords);

        // 4. Run recalculations
        $this->runCalculations($project);

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui dan dihitung ulang!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        abort_if($project->user_id !== auth()->id(), 403, 'Akses ditolak.');

        $project->delete(); // Cascade deletes related tables due to migration constraint

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus!');
    }

    /**
     * Private helper to run all production forecasts and cash flow calculations.
     */
    private function runCalculations(Project $project)
    {
        // 1. Get actual production data from DB
        $actualData = $project->productionData()
            ->where('is_predicted', false)
            ->orderBy('year')
            ->get();

        $knownYears = $actualData->pluck('year')->toArray();
        $knownProd = $actualData->pluck('production')->toArray();

        // 2. Forecast production using PredictionService
        $predResult = $this->predictionService->predict(
            $knownYears,
            $knownProd,
            $project->known_years + $project->prediction_years,
            $project->decline_rate,
            $project->decline_start_year,
            $project->total_reserve
        );

        // 3. Save forecasted production data to DB
        // Clear any old predictions first
        $project->productionData()->where('is_predicted', true)->delete();

        $predictionRecords = [];
        foreach ($predResult['predictions'] as $year => $prod) {
            if (!in_array($year, $knownYears)) {
                $predictionRecords[] = [
                    'project_id' => $project->id,
                    'year' => $year,
                    'production' => $prod,
                    'is_predicted' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($predictionRecords)) {
            $project->productionData()->insert($predictionRecords);
        }

        // 4. Calculate Net Cash Flows (from Year 0 to N)
        $cashFlows = $this->cashFlowService->calculateCashFlow($project, $predResult['predictions']);

        // 5. Save results to calculations table in database
        $project->calculations()->delete(); // Clear old calculations first
        
        $calculationRecords = [];
        foreach ($cashFlows as $row) {
            $row['project_id'] = $project->id;
            $row['created_at'] = now();
            $row['updated_at'] = now();
            $calculationRecords[] = $row;
        }
        if (!empty($calculationRecords)) {
            $project->calculations()->insert($calculationRecords);
        }
    }
}
