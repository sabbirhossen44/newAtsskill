<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\CourseRepository;
use App\Repositories\OrderRepository;
use App\Repositories\WithdrawRepository;
use Illuminate\View\View;

class InstructorDashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth('web')->user()->id;
        $totalCourses = CourseRepository::countForInstructor($userId);
        $totalPendingCourses = CourseRepository::countPendingForInstructor($userId);
        $courseIds = CourseRepository::instructorCourseIds($userId);
        $totalOrders = OrderRepository::countSoldForCourseIds($courseIds);
        $totalPendingOrders = OrderRepository::countPendingOrdersForCourseIds($courseIds);
        $totalWithdraw = WithdrawRepository::totalApprovedForUser($userId);

        return view('frontend.instructor-dashboard.index', compact(
            'totalCourses',
            'totalOrders',
            'totalPendingCourses',
            'totalPendingOrders',
            'totalWithdraw'
        ));
    }

    function mySells()
    {
        $orders = OrderRepository::mySellsForInstructor(auth('web')->user()->id);

        return view('frontend.instructor-dashboard.my-sells.index', compact('orders'));
    }
}
