<?php

namespace App\Http\Controllers;

use App\Models\FooterCoupon;
use App\Models\FooterSetting;

class CouponPrintController extends Controller
{
    public function __invoke(FooterCoupon $footerCoupon)
    {
        abort_unless(FooterSetting::get()->coupons_enabled, 404);

        return view('coupons.print', ['coupon' => $footerCoupon]);
    }
}
