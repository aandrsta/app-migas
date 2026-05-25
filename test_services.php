<?php

// Include autoloader
require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel Application so we can use Eloquent models
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Services\PredictionService;
use App\Services\DepreciationService;
use App\Services\CashFlowService;
use App\Services\EconomicIndicatorService;

$predictionService = new PredictionService();
$depreciationService = new DepreciationService();
$cashFlowService = new CashFlowService($depreciationService);
$economicIndicatorService = new EconomicIndicatorService();

echo "=============================================\n";
echo "OIL & GAS SERVICES CALCULATION VERIFICATION\n";
echo "=============================================\n\n";

// Setup project with exact parameters from Contoh Perhitungan Excel
$project = new Project([
    'name' => 'Lapangan Test',
    'oil_price' => 32.0,
    'capital_cost' => 13000.0,
    'non_capital_cost' => 8000.0,
    'opex_per_year' => 180.0,
    'tax_rate' => 51.0,
    'discount_rate' => 10.0,
    'known_years' => 4,
    'prediction_years' => 16,
    'depreciation_years' => 20,
    'depreciation_method' => 'straight_line',
]);

// 1. TEST A: VERIFY CASH FLOW WITH EXACT SPREADSHEET PRODUCTION DATA
// This is to verify our cash flow math matches the target NCF Total ≈ $18,069.82M
echo "--- TEST A: EXACT SPREADSHEET DATA VERIFICATION ---\n";

$spreadsheetProduction = [
    1 => 175.0,
    2 => 201.0,
    3 => 217.0,
    4 => 198.0,
    5 => 192.06,
    6 => 186.29,
    7 => 180.7013,
    8 => 175.280261,
    9 => 170.0218532,
    10 => 164.9211976,
    11 => 159.9735616,
    12 => 155.1743548,
    13 => 150.5191242,
    14 => 146.0035504,
    15 => 141.6234439,
    16 => 137.3747406,
    17 => 133.2534984,
    18 => 129.2558934,
    19 => 125.3782166,
    20 => 121.6168701
];

$cashFlow = $cashFlowService->calculateCashFlow($project, $spreadsheetProduction);

// Print first few rows of cash flow
echo "Year 0: NCF = " . $cashFlow[0]['ncf'] . " | Cum = " . $cashFlow[0]['cumulative_ncf'] . "\n";
echo "Year 1: Prod = " . $cashFlow[1]['production'] . " | Inc = " . $cashFlow[1]['income'] . " | Di = " . $cashFlow[1]['depreciation'] . " | Tax = " . $cashFlow[1]['tax'] . " | NCF = " . $cashFlow[1]['ncf'] . "\n";
echo "Year 2: Prod = " . $cashFlow[2]['production'] . " | Inc = " . $cashFlow[2]['income'] . " | Di = " . $cashFlow[2]['depreciation'] . " | Tax = " . $cashFlow[2]['tax'] . " | NCF = " . $cashFlow[2]['ncf'] . "\n";
echo "...\n";
echo "Year 20: Prod = " . $cashFlow[20]['production'] . " | Inc = " . $cashFlow[20]['income'] . " | Di = " . $cashFlow[20]['depreciation'] . " | Tax = " . $cashFlow[20]['tax'] . " | NCF = " . $cashFlow[20]['ncf'] . "\n";

// Sum all NCF
$totalNcf = 0.0;
foreach ($cashFlow as $row) {
    $totalNcf += $row['ncf'];
}
echo "\nNCF Total (calculated): $" . number_format($totalNcf, 5) . " M\n";
echo "NCF Total (target):     $18,069.82254 M\n";

$difference = abs($totalNcf - 18069.82254);
if ($difference < 0.01) {
    echo "✅ SUCCESS: Cash flow matches the excel example exactly!\n\n";
} else {
    echo "❌ ERROR: Cash flow mismatch. Diff: " . $difference . "\n\n";
}

// Calculate indicators for exact data
$npv = $economicIndicatorService->calculateNpv($cashFlow);
$irr = $economicIndicatorService->calculateIrr($cashFlow);
$pot = $economicIndicatorService->calculatePot($cashFlow);
$pir = $economicIndicatorService->calculatePir($cashFlow);
$dpr = $economicIndicatorService->calculateDpr($cashFlow);
$feasibility = $economicIndicatorService->checkFeasibility($npv, $irr, $project->discount_rate);

echo "Economic Indicators for Test A:\n";
echo "- POT (Pay Out Time):  " . ($pot ?? 'Never') . " years\n";
echo "- NPV (Net Present Value): $" . number_format($npv, 2) . " M\n";
echo "- IRR / ROR:           " . $irr . " %\n";
echo "- PIR:                 " . $pir . "\n";
echo "- DPR:                 " . $dpr . "\n";
echo "- Feasibility:         " . $feasibility['status'] . "\n\n";


// 2. TEST B: VERIFY END-TO-END WITH LINEAR REGRESSION PREDICTION
echo "--- TEST B: END-TO-END WITH LINEAR REGRESSION PREDICTION ---\n";

$knownYears = [1, 2, 3, 4];
$knownProd = [175.0, 201.0, 217.0, 198.0];

$predResult = $predictionService->predict($knownYears, $knownProd, 20);

echo "Linear Regression Parameters: m = " . $predResult['linear']['m'] . ", b = " . $predResult['linear']['b'] . ", R2 = " . $predResult['linear']['r_squared'] . "\n";
echo "Quadratic Regression Parameters: a = " . $predResult['quadratic']['a'] . ", b = " . $predResult['quadratic']['b'] . ", c = " . $predResult['quadratic']['c'] . ", R2 = " . $predResult['quadratic']['r_squared'] . "\n";

echo "Predicted Production (Year 5):  " . $predResult['predictions'][5] . " MBBL\n";
echo "Predicted Production (Year 20): " . $predResult['predictions'][20] . " MBBL\n";

$cashFlowB = $cashFlowService->calculateCashFlow($project, $predResult['predictions']);
$totalNcfB = 0.0;
foreach ($cashFlowB as $row) {
    $totalNcfB += $row['ncf'];
}

$npvB = $economicIndicatorService->calculateNpv($cashFlowB);
$irrB = $economicIndicatorService->calculateIrr($cashFlowB);
$potB = $economicIndicatorService->calculatePot($cashFlowB);
$pirB = $economicIndicatorService->calculatePir($cashFlowB);
$dprB = $economicIndicatorService->calculateDpr($cashFlowB);

echo "\nNCF Total (calculated with regression): $" . number_format($totalNcfB, 2) . " M\n";
echo "Economic Indicators for Test B:\n";
echo "- POT:  " . ($potB ?? 'Never') . " years\n";
echo "- NPV:  $" . number_format($npvB, 2) . " M\n";
echo "- IRR:  " . $irrB . " %\n";
echo "- PIR:  " . $pirB . "\n";
echo "- DPR:  " . $dprB . "\n";
echo "=============================================\n";
