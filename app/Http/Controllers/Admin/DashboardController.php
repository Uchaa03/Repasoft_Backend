<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Get Dashboard data
    public function getDashboardMetrics()
    {
        // Addition for total_earnings and total_losses
        $totalIncome = Store::all()->sum('total_earnings');
        $totalExpense = Store::all()->sum('total_losses');

        // Data of last 30 days of repairs in warranty and out of warranty
        $dailyFinancialStats = DB::table('repairs')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN is_warranty = false AND status = \'completed\' THEN total_cost ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN is_warranty = true AND status = \'completed\' THEN total_cost ELSE 0 END) as expense')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Other stats
        $numberOfStores = Store::count();
        $numberOfActiveTechnicians = User::role('technician')->count();
        $totalNumberOfRepairs = DB::table('repairs')->count();

        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'daily_financial_stats' => $dailyFinancialStats,
            'stores_count' => $numberOfStores,
            'active_technicians_count' => $numberOfActiveTechnicians,
            'total_repairs_count' => $totalNumberOfRepairs,
        ]);
    }
}
