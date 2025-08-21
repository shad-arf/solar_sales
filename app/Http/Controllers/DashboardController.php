<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Income;
use App\Models\Expense;
use App\Models\OwnerEquity;
use App\Models\ItemSale;
use App\Models\InventoryAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'businessSummary' => $this->getBusinessSummary(),
            'financialSummary' => $this->getFinancialSummary(),
            'inventoryStats' => $this->getInventoryStats(),
            'salesStats' => $this->getSalesStats(),
            'purchaseStats' => $this->getPurchaseStats(),
            'monthlyData' => $this->getMonthlyData(),
            'balanceSheet' => $this->getBalanceSheet(),
            'incomeStatement' => $this->getIncomeStatement(),
            'ownerEquity' => $this->getOwnerEquity(),
            'recentActivity' => $this->getRecentActivity(),
            'businessWorth' => $this->getBusinessWorth(),
            'categoryBreakdown' => $this->getCategoryBreakdown(),
            'actualProfitLoss' => $this->getActualProfitLoss(),
            'inventoryAdjustments' => $this->getInventoryAdjustmentStats()
        ];

        return view('dashboard.index', $data);
    }

    private function getFinancialSummary()
    {
        $totalRevenue = Income::getTotalThisMonth();
        $totalExpenses = Expense::getTotalThisMonth();
        $netIncome = $totalRevenue - $totalExpenses;
        
        // Calculate inventory value as assets
        $inventoryValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        $ownerEquity = OwnerEquity::getNetEquity();
        $totalAssets = $inventoryValue + $ownerEquity;

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'total_assets' => $totalAssets
        ];
    }

    private function getMonthlyData()
    {
        $months = [];
        $revenues = [];
        $expenses = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $revenue = Income::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');

            $expense = Expense::whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->sum('amount');

            $revenues[] = $revenue ?: 0;
            $expenses[] = $expense ?: 0;
        }

        return [
            'months' => $months,
            'revenues' => $revenues,
            'expenses' => $expenses
        ];
    }

    private function getBalanceSheet()
    {
        // Simple balance sheet with basic business items
        $inventoryValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        $cashOnHand = Income::getTotalThisYear() - Expense::getTotalThisYear();
        
        $assets = collect([
            ['name' => 'Cash', 'balance' => max(0, $cashOnHand)],
            ['name' => 'Inventory', 'balance' => $inventoryValue],
        ]);

        $liabilities = collect([
            ['name' => 'Accounts Payable', 'balance' => 0], // Can be manually entered later
        ]);

        $ownerEquity = OwnerEquity::getNetEquity();
        $equity = collect([
            ['name' => 'Owner Equity', 'balance' => $ownerEquity],
        ]);

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totals' => [
                'assets' => $totalAssets,
                'liabilities' => $totalLiabilities,
                'equity' => $totalEquity
            ]
        ];
    }

    private function getIncomeStatement()
    {
        // Get income by category for current year
        $revenues = collect();
        foreach (Income::CATEGORIES as $key => $name) {
            $amount = Income::where('category', $key)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount');
            if ($amount > 0) {
                $revenues->push(['name' => $name, 'balance' => $amount]);
            }
        }

        // Get expenses by category for current year
        $expenses = collect();
        foreach (Expense::CATEGORIES as $key => $name) {
            $amount = Expense::where('category', $key)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount');
            if ($amount > 0) {
                $expenses->push(['name' => $name, 'balance' => $amount]);
            }
        }

        $totalRevenue = $revenues->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totals' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'net_income' => $netIncome
            ]
        ];
    }

    private function getOwnerEquity()
    {
        return [
            'total_investments' => OwnerEquity::getTotalInvestments(),
            'total_drawings' => OwnerEquity::getTotalDrawings(),
            'net_equity' => OwnerEquity::getNetEquity(),
            'recent_transactions' => OwnerEquity::orderBy('transaction_date', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    private function getBusinessSummary()
    {
        // Calculate total business worth
        $inventoryValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        $totalIncome = Income::getTotalThisYear();
        $totalExpenses = Expense::getTotalThisYear();
        $ownerInvestment = OwnerEquity::getTotalInvestments();
        $ownerDrawings = OwnerEquity::getTotalDrawings();
        
        // Net profit from operations
        $netProfit = $totalIncome - $totalExpenses;
        
        // Total business worth = inventory + cash from operations + owner investment - drawings
        $businessWorth = $inventoryValue + $netProfit + $ownerInvestment - $ownerDrawings;
        
        return [
            'business_worth' => $businessWorth,
            'inventory_value' => $inventoryValue,
            'net_profit' => $netProfit,
            'owner_equity' => OwnerEquity::getNetEquity()
        ];
    }

    private function getInventoryStats()
    {
        $totalItems = Item::count();
        $totalQuantity = Item::sum('quantity') ?: 0;
        $lowStockItems = Item::where('quantity', '<=', 10)->count();
        $outOfStockItems = Item::where('quantity', 0)->count();
        $totalValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        $averageItemValue = $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_value' => $totalValue,
            'average_item_value' => $averageItemValue,
            'items_in_stock' => $totalItems - $outOfStockItems
        ];
    }

    private function getSalesStats()
    {
        // Using Income as sales data for now
        $totalSales = Income::getTotalThisYear();
        $monthlySales = Income::getTotalThisMonth();
        $salesCount = Income::count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
        
        // Calculate growth
        $lastMonthSales = Income::whereMonth('date', Carbon::now()->subMonth()->month)
            ->whereYear('date', Carbon::now()->subMonth()->year)
            ->sum('amount');
        
        $salesGrowth = $lastMonthSales > 0 ? (($monthlySales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        return [
            'total_sales' => $totalSales,
            'monthly_sales' => $monthlySales,
            'sales_count' => $salesCount,
            'average_sale' => $averageSale,
            'sales_growth' => $salesGrowth
        ];
    }

    private function getPurchaseStats()
    {
        $totalPurchases = Purchase::sum(DB::raw('total_amount')) ?: 0;
        $monthlyPurchases = Purchase::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount') ?: 0;
        $purchaseCount = Purchase::count();
        $averagePurchase = $purchaseCount > 0 ? $totalPurchases / $purchaseCount : 0;
        $pendingPurchases = Purchase::where('status', 'pending')->count();
        $completedPurchases = Purchase::where('status', 'completed')->count();

        return [
            'total_purchases' => $totalPurchases,
            'monthly_purchases' => $monthlyPurchases,
            'purchase_count' => $purchaseCount,
            'average_purchase' => $averagePurchase,
            'pending_purchases' => $pendingPurchases,
            'completed_purchases' => $completedPurchases
        ];
    }

    private function getBusinessWorth()
    {
        $inventoryValue = Item::sum(DB::raw('quantity * COALESCE(price, 0)')) ?: 0;
        $totalIncome = Income::getTotalThisYear();
        $totalExpenses = Expense::getTotalThisYear();
        $ownerInvestment = OwnerEquity::getTotalInvestments();
        $ownerDrawings = OwnerEquity::getTotalDrawings();
        
        // Assets
        $assets = [
            'inventory' => $inventoryValue,
            'cash_from_operations' => $totalIncome - $totalExpenses,
            'owner_investment' => $ownerInvestment
        ];
        
        // Liabilities
        $liabilities = [
            'owner_drawings' => $ownerDrawings
        ];
        
        $totalAssets = array_sum($assets);
        $totalLiabilities = array_sum($liabilities);
        $netWorth = $totalAssets - $totalLiabilities;
        
        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_worth' => $netWorth
        ];
    }

    private function getRecentActivity()
    {
        // Get recent income and expenses
        $recentIncomes = Income::orderBy('date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($income) {
                return (object)[
                    'type' => 'income',
                    'amount' => $income->amount,
                    'description' => $income->description,
                    'category' => Income::CATEGORIES[$income->category] ?? $income->category,
                    'date' => $income->date,
                ];
            });

        $recentExpenses = Expense::orderBy('date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($expense) {
                return (object)[
                    'type' => 'expense',
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'category' => Expense::CATEGORIES[$expense->category] ?? $expense->category,
                    'date' => $expense->date,
                ];
            });

        return $recentIncomes->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(10);
    }

    private function getCategoryBreakdown()
    {
        // Get real income category data
        $incomeCategories = [];
        $incomeLabels = [];
        $incomeData = [];
        
        foreach (Income::CATEGORIES as $key => $name) {
            $amount = Income::where('category', $key)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount');
            if ($amount > 0) {
                $incomeLabels[] = $name;
                $incomeData[] = $amount;
            }
        }
        
        // Get real expense category data
        $expenseCategories = [];
        $expenseLabels = [];
        $expenseData = [];
        
        foreach (Expense::CATEGORIES as $key => $name) {
            $amount = Expense::where('category', $key)
                ->whereYear('date', Carbon::now()->year)
                ->sum('amount');
            if ($amount > 0) {
                $expenseLabels[] = $name;
                $expenseData[] = $amount;
            }
        }
        
        return [
            'income' => [
                'labels' => $incomeLabels,
                'data' => $incomeData
            ],
            'expense' => [
                'labels' => $expenseLabels,
                'data' => $expenseData
            ]
        ];
    }

    private function getActualProfitLoss()
    {
        // Sales revenue data only (cost tracking not available in current table structure)
        $totalSalesRevenue = ItemSale::getTotalSalesRevenue();
        $monthlySalesRevenue = ItemSale::getTotalSalesRevenueThisMonth();
        
        // For now, set item costs to 0 since cost tracking is not available
        $totalItemCosts = 0;
        
        // Calculate actual profit
        $actualProfit = $totalSalesRevenue - $totalItemCosts;
        $monthlyActualProfit = $monthlySalesRevenue - 0; // No monthly cost tracking yet
        
        // Calculate profit margin
        $profitMargin = $totalSalesRevenue > 0 ? (($actualProfit / $totalSalesRevenue) * 100) : 0;
        
        $monthlyData = ItemSale::getMonthlyRevenueData();
        
        return [
            'total_sales_revenue' => $totalSalesRevenue,
            'monthly_sales_revenue' => $monthlySalesRevenue,
            'total_item_costs' => $totalItemCosts,
            'actual_profit' => $actualProfit,
            'monthly_actual_profit' => $monthlyActualProfit,
            'profit_margin' => $profitMargin,
            'monthly_data' => $monthlyData
        ];
    }

    private function getInventoryAdjustmentStats()
    {
        return [
            'total_adjustments' => InventoryAdjustment::count(),
            'total_financial_impact' => InventoryAdjustment::getTotalFinancialImpact(),
            'monthly_financial_impact' => InventoryAdjustment::getTotalFinancialImpactThisMonth(),
            'adjustments_by_reason' => InventoryAdjustment::getAdjustmentsByReason(),
            'recent_adjustments' => InventoryAdjustment::with('item')
                ->orderBy('adjustment_date', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    public function addOwnerEquity(Request $request)
    {
        $request->validate([
            'type' => 'required|in:investment,drawing',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:50'
        ]);

        OwnerEquity::create($request->all());

        return redirect()->route('dashboard')
            ->with('success', 'Owner equity transaction added successfully!');
    }
}