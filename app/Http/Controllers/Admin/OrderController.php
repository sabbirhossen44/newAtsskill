<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

/**
 * Admin orders. Old behaviour kept, including the wallet/enrolment
 * side effects of updateOrder(): marking an order paid enrols the
 * buyer and credits the instructor (minus commission); any other
 * payment status removes the enrolment and debits the wallet.
 * Enum casts are compared against enum cases, never strings.
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        Paginator::useBootstrap();
        $orders = OrderRepository::paginateAdminOrders($request->input('keyword'), $this->perPage($request));

        return view('admin.order.index', ['orders' => $orders, 'title' => __('Order History')]);
    }

    public function pending_order()
    {
        Paginator::useBootstrap();
        $orders = OrderRepository::paginatePendingOrders($this->perPage(request()));

        return view('admin.order.pending-orders', ['orders' => $orders, 'title' => __('Pending Order')]);
    }

    public function show($id)
    {
        $order = OrderRepository::findAdminOrFail($id);

        return view('admin.order.show', ['order' => $order]);
    }

    public function updateOrder(Request $request, $id)
    {
        $order = OrderRepository::findByIdOrFail($id);

        OrderRepository::updateAdminOrder($order, [
            'status' => $request->input('order_status'),
            'payment_status' => $request->input('payment_status'),
        ]);
        $order->refresh();

        foreach ($order->orderItems as $item) {
            $commissionAmount = $item->price * ($order->commission_rate / 100);
            $amountAfterCommission = $item->price - $commissionAmount;
            $instructor = Course::find($item->course_id)?->instructor;

            if ($order->payment_status === PaymentStatusEnum::PAID) {
                Enrollment::firstOrCreate([
                    'order_id' => $order->id,
                    'user_id' => $order->buyer_id,
                    'course_id' => $item->course_id,
                ], ['has_access' => 1]);
                $instructor?->increment('wallet_balance', $amountAfterCommission);
            } else {
                Enrollment::where('user_id', $order->buyer_id)->where('course_id', $item->course_id)->delete();
                $instructor?->decrement('wallet_balance', $amountAfterCommission);
            }
        }

        return redirect()->back()->with('success', __('order status updated successfully'));
    }

    public function printInvoice(Request $request, $id)
    {
        $order = OrderRepository::findAdminOrFail($id);

        return view('admin.order.invoice', ['order' => $order]);
    }

    public function destroy($id)
    {
        $order = OrderRepository::findByIdOrFail($id);

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            foreach ($order->orderItems as $item) {
                Enrollment::where('user_id', $order->buyer_id)->where('course_id', $item->course_id)->delete();
                $commissionAmount = $item->price * ($order->commission_rate / 100);
                $amountAfterCommission = $item->price - $commissionAmount;
                Course::find($item->course_id)?->instructor?->decrement('wallet_balance', $amountAfterCommission);
            }
        }
        $order->orderItems()->delete();
        $order->delete();

        return redirect()->route('admin.orders')->with('success', __('Order deleted successfully'));
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 15;
    }
}
