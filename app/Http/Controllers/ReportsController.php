<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceMatch;
use App\Models\Rating;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function stats()
    {
        $now = Carbon::now();

        $totalUsers           = User::count();
        $newUsersThisMonth    = User::whereMonth('created_at', $now->month)
                                    ->whereYear('created_at', $now->year)->count();

        $totalServices        = Service::count();
        $newServicesThisMonth = Service::whereMonth('created_at', $now->month)
                                        ->whereYear('created_at', $now->year)->count();

        $totalCoins           = ServiceMatch::where('status', 'completed')->sum('deducted_amount');
        $coinsThisMonth       = ServiceMatch::where('status', 'completed')
                                             ->whereMonth('updated_at', $now->month)
                                             ->whereYear('updated_at', $now->year)
                                            ->sum('deducted_amount');

        $avgRating            = Rating:: avg('stars') ?? 0;

        $completedServices    = ServiceMatch::where('status', 'completed')
                                             ->whereMonth('updated_at', $now->month)
                                             ->whereYear('updated_at', $now->year)->count();

        $reportedIssues       = Report::where('status', 'pending')->count();

        return response()->json([
            'total_users'             => $totalUsers,
            'new_users_this_month'    => $newUsersThisMonth,
            'active_services'         => $totalServices,
            'new_services_this_month' => $newServicesThisMonth,
            'time_coins_transferred'  => $totalCoins,
            'coins_this_month'        => $coinsThisMonth,
            'avg_rating'              => round($avgRating, 1),
            'completed_services'      => $completedServices,
            'reported_issues'         => $reportedIssues,
        ]);
    }

    public function monthlyUsers()
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $volunteers = User::where('role', 'volunteer')
                              ->whereMonth('created_at', $month->month)
                              ->whereYear('created_at', $month->year)
                              ->count();

            $customers  = User::where('role', 'customer')
                              ->whereMonth('created_at', $month->month)
                              ->whereYear('created_at', $month->year)
                              ->count();

            $data[] = [
                'month'      => $month->format('M'),
                'volunteers' => $volunteers,
                'customers'  => $customers,
            ];
        }

        return response()->json($data);
    }

    public function topServices()
    {
        $services = Service::select(
                        'category_id',
                        DB::raw('COUNT(*) as total_requests'),
                        DB::raw('SUM(CASE WHEN service_matches.status = "completed" THEN 1 ELSE 0 END) as completed'),
                        DB::raw('SUM(CASE WHEN service_matches.status = "in_progress" THEN 1 ELSE 0 END) as in_progress')
                    )
                    ->leftJoin('service_matches', 'services.id', '=', 'service_matches.service_id')
                    ->groupBy('category_id')
                    ->with('category:id,en_name,ar_name')
                    ->orderByDesc('total_requests')
                    ->limit(5)
                    ->get();

                   $result = $services->map(function ($s) {
                      $avgRating = Rating::where(function($q) use ($s) {
               $q->whereIn('servicematch_id', 
                   ServiceMatch::whereHas('service', function($q2) use ($s) {
                       $q2->where('category_id', $s->category_id);
                   })->pluck('id')
               );
           })->avg('stars');

            return [
                'category'    => $s->category->en_name ?? 'Other',
                'requests'    => $s->total_requests,
                'completed'   => $s->completed ?? 0,
                'in_progress' => $s->in_progress ?? 0,
                'rating'      => round($avgRating ?? 0, 1),
            ];
        });

        return response()->json($result);
    }

    public function coinsMovement()
    {
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $earned = ServiceMatch::where('status', 'completed')
                                   ->whereMonth('updated_at', $month->month)
                                   ->whereYear('updated_at', $month->year)
                                   ->sum('deducted_amount');

        
            $spent = $earned;

            $data[] = [
                'month'  => $month->format('M'),
                'earned' => $earned,
                'spent'  => $spent,
            ];
        }

        return response()->json($data);
    }


    public function issuesReport()
    {
        $issues = Report::with([
                      'reporter:id,name,email',
                      'serviceMatch:id,service_id,status'
                  ])
                  ->orderByDesc('created_at')
                  ->limit(20)
                  ->get()
                  ->map(function ($r) {
                      return [
                          'id'           => $r->id,
                          'reported_by'  => $r->reporter->name ?? 'Unknown',
                          'email'        => $r->reporter->email ?? '',
                          'reason'       => $r->reason,
                          'status'       => $r->status,
                          'created_at' => $r->created_at ? $r->created_at->format('Y-m-d') : null,
                      ];
                  });

        return response()->json($issues);
    }
}