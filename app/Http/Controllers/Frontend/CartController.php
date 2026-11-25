<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ApplyCouponRequest;
use App\Repositories\CourseRepository;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        return view('frontend.pages.cart.index', $this->cart->summary());
    }

    public function addToCart(Request $request, string $id)
    {
        if ($this->cart->containsCourse($id)) {
            return response(['status' => 'error', 'message' => 'Already added to cart!']);
        }
        if ($this->cart->isOwnCourse($id, auth('web')->user()?->id)) {
            return response(['status' => 'error', 'message' => 'You can not add to cart your own course!']);
        }
        $course = CourseRepository::findActiveById($id);

        $cartData = CartService::cartDataForCourse($course);
        $this->cart->addCourse($course);

        $response = ['status' => 'success', 'message' => 'Added to cart successfully!', 'cart_count' => $this->cart->content()->count()];

        if ($dataLayer = $this->cart->addToCartDataLayer($course, $cartData)) {
            $response['dataLayer'] = $dataLayer;
        }

        return response($response);
    }

    public function removeCartItem(string $rowId)
    {
        $removed = $this->cart->remove($rowId);

        if ($removed && $dataLayer = $this->cart->removeFromCartDataLayer($removed)) {
            session()->put('removeFromCart', $dataLayer);
        }

        $notification = [
            'messege' => __('Item removed from cart!'),
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    public function applyCoupon(ApplyCouponRequest $request)
    {
        $result = $this->cart->applyCoupon($request->coupon);

        if (! $result['ok']) {
            return response()->json(['message' => $result['message']], 403);
        }

        return response()->json(array_merge(
            ['message' => $result['message']],
            $this->cart->couponResponseData()
        ));
    }

    public function removeCoupon()
    {
        $this->cart->destroyCouponSession();

        $notification = [
            'messege' => __('Coupon removed successfully!'),
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    public function buyNow(string $slug)
    {
        $course = CourseRepository::findActiveBySlugOrFail($slug);

        if ($this->cart->isOwnCourse((string) $course->id, auth('web')->user()?->id)) {
            return redirect()->back()->with(['messege' => 'You can not buy your own course!', 'alert-type' => 'error']);
        }

        if (! $this->cart->containsCourse((string) $course->id)) {
            $this->cart->addCourse($course);
        }

        if (! auth('web')->check()) {
            session()->put('url.intended', route('checkout.index'));

            return redirect()->route('register');
        }

        return redirect()->route('checkout.index');
    }
}
