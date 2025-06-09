<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardMetrics()
    {
        // Only Admin Stores
        $stores = Store::where('admin_id', auth()->id())->get();
        $totalIncome = $stores->sum('total_earnings');
        $totalExpense = $stores->sum('total_losses');

        // Last 30 days stats
        $dailyFinancialStats = DB::table('repairs')
            ->join('stores', 'stores.id', '=', 'repairs.store_id')
            ->where('stores.admin_id', auth()->id())
            ->select(
                DB::raw('DATE(repairs.created_at) as date'),
                DB::raw('SUM(CASE WHEN repairs.is_warranty = false AND repairs.status = \'completed\' THEN repairs.total_cost ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN repairs.is_warranty = true AND repairs.status = \'completed\' THEN repairs.total_cost ELSE 0 END) as expense')
            )
            ->where('repairs.created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Other stats
        $numberOfStores = $stores->count();
        $numberOfActiveTechnicians = User::role('technician')
            ->where('admin_id', auth()->id())
            ->count();
        $totalNumberOfRepairs = DB::table('repairs')
            ->join('stores', 'stores.id', '=', 'repairs.store_id')
            ->where('stores.admin_id', auth()->id())
            ->count();

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
