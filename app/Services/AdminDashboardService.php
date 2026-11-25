<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Order;
use App\Repositories\LanguageRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Admin dashboard aggregates + the admin language switcher, moved
 * out of DashboardController so the controller only returns the view.
 * All figures and keys are exactly as the old controller computed them.
 */
class AdminDashboardService
{
    public static function dashboardData(Request $request): array
    {
        $orders = Order::query()
            ->select(['id', 'coupon_discount_amount', 'payable_amount', 'gateway_charge', 'commission_rate', 'created_at'])
            ->where('payment_status', 'paid')
            ->get();

        $totalEarnings = 0;
        $thisYearsEarnings = 0;
        $thisMonthsEarnings = 0;
        $todaysEarnings = 0;

        foreach ($orders as $order) {
            if ($order->commission_rate > 0) {
                $total = ($order->payable_amount + $order->gateway_charge) - $order->coupon_discount_amount;
                $commission = $total * ($order->commission_rate / 100);
                $totalEarnings += $commission;

                if (date('Y') == date('Y', strtotime($order->created_at))) {
                    $thisYearsEarnings += $commission;
                }

                if (date('m') == date('m', strtotime($order->created_at)) && date('Y') == date('Y', strtotime($order->created_at))) {
                    $thisMonthsEarnings += $commission;
                }

                if (date('Y-m-d') == date('Y-m-d', strtotime($order->created_at))) {
                    $todaysEarnings += $commission;
                }
            }
        }

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        if ($request->filled('year') && $request->filled('month')) {
            $year = $request->input('year');
            $month = $request->input('month');

            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } elseif ($request->filled('year')) {
            $year = $request->input('year');

            $start = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
        }

        $dataItems = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(payable_amount) as total_price, SUM(gateway_charge) as gateway_charge, SUM(coupon_discount_amount) as coupon_discount_amount')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->get();

        $dates = [];

        while ($start <= $end) {
            $dates[] = $start->toDateString();
            $start->addDay();
        }

        $dataCal = array_fill_keys($dates, 0);

        foreach ($dataItems as $item) {
            $dataCal[$item->date] = ($item->total_price + $item->gateway_charge) - $item->coupon_discount_amount;
        }

        $data = [];
        $data['monthly_data'] = json_encode(array_values($dataCal));
        $data['oldestYear'] = Carbon::parse(Order::query()->orderBy('created_at', 'asc')->first()?->created_at)->year ?? Carbon::now()->year;
        $data['latestYear'] = Carbon::parse(Order::query()->orderBy('created_at', 'desc')->first()?->created_at)->year ?? Carbon::now()->year;
        $data['total_orders'] = Order::query()->count();
        $data['total_pending_orders'] = Order::query()->where(['status' => 'pending'])->count();
        $data['total_course'] = Course::query()->count();
        $data['total_pending_course'] = Course::query()->where(['status' => 'pending', 'is_approved' => 'approved'])->count();
        $data['total_earning'] = $totalEarnings;
        $data['this_months_earning'] = $thisMonthsEarnings;
        $data['todays_earning'] = $todaysEarnings;
        $data['this_years_earning'] = $thisYearsEarnings;
        $data['recent_courses'] = Course::query()->orderBy('created_at', 'desc')->limit(5)->get();
        $data['pending_courses'] = Course::query()->where('is_approved', 'pending')->orderBy('created_at', 'desc')->count();
        $data['recent_blogs'] = Blog::query()->orderBy('created_at', 'desc')->limit(5)->get();
        $data['pending_blogs'] = Blog::query()->where('status', 0)->orderBy('created_at', 'desc')->count();
        $data['recent_contacts'] = ContactMessage::query()->orderBy('created_at', 'desc')->limit(5)->get();

        return $data;
    }

    /**
     * Switch the admin session language and clear the cached menus,
     * as the old DashboardController::setLanguage did.
     */
    public static function setLanguage(?string $code): void
    {
        // clear menu cache
        Cache::forget('nav_menu');
        Cache::forget('footer_menu_one');
        Cache::forget('footer_menu_two');
        Cache::forget('footer_menu_three');
        Cache::forget('getSocialLinks');

        $lang = $code ? LanguageRepository::findByCode($code) : null;

        if (session()->has('lang')) {
            session()->forget('lang');
            session()->forget('text_direction');
        }

        if ($lang) {
            session()->put('lang', $lang->code);
            session()->put('text_direction', $lang->direction);

            return;
        }

        session()->put('lang', config('app.locale'));
    }
}
