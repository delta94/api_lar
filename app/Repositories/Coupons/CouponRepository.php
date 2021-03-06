<?php

namespace App\Repositories\Coupons;

use App\Repositories\BaseRepository;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    /**
     * Coupon model.
     * @param Coupon $coupon
     */
    public function __construct(Coupon $coupon)
    {
        $this->model    = $coupon;
    }

    /**
     * Lấy dữ liệu theo mã Coupon
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param string $code
     *
     * @return mixed
     */
    public function getCouponByCode(string $code)
    {
        return $this->model->where('code', $code)->with('promotion')->first();
    }

    /**
     *
     */

    public function getAllExpiredCoupon()
    {
        return $this->model->where([['promotion_id', null],['status',1],['usable','>',0]])->get();
    }
}
