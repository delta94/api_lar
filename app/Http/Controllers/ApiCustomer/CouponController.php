<?php

namespace App\Http\Controllers\ApiCustomer;

use App\Http\Transformers\CouponTransformer;
use App\Repositories\_Customer\CouponLogic;
use App\Repositories\Coupons\Coupon;
use App\Repositories\Coupons\CouponRepository;
use App\Repositories\Coupons\CouponRepositoryInterface;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends ApiController
{
    protected $validationRules    = [
        'code'                 => 'required|string|min:4|unique:coupons,code',
        'discount'             => 'required|integer|between:0,100',
        'max_discount'         => 'integer|min:0',
        'usable'               => 'integer|min:0',
        'used'                 => 'integer|min:0',
        'status'               => 'required|integer|between:0,1',
        'settings'             => 'min:1',
        'settings.rooms.*'     => 'distinct|exists:rooms,id,deleted_at,NULL',
        'settings.cities.*'    => 'distinct|exists:cities,id,deleted_at,NULL',
        'settings.districts.*' => 'distinct|exists:districts,id,deleted_at,NULL',
        'settings.days.*'      => 'distinct|date|after:now',
        'promotion_id'         => 'required|integer|exists:promotions,id,deleted_at,NULL',
        'coupon'               => 'string|min:4|exists:coupons,code,deleted_at,NULL',
    ];
    protected $validationMessages = [
        'code.required'                 => 'Mã giảm giá không được để trống',
        'code.string'                   => 'Mã giảm giá không được chứa ký tự đặc biệt',
        'code.min'                      => 'Độ dài phải là :min',
        'code.unique'                   => 'Mã giảm giá này đã tồn tại',
        'discount.required'             => 'Phần trăm giảm giá không được để trống',
        'discount.integer'              => 'Phần trăm giảm giá không phải là dạng số',
        'discount.between'              => 'Phần trăm giảm giá không phù hợp',
        'max_discount.integer'          => 'Số tiền tối đa được giảm không phải là số',
        'max_discount.min'              => 'Số tiền tối đa được giảm không được dưới 0',
        'usable.integer'                => 'Số lần sử dụng tối đa không phải là số',
        'usable.min'                    => 'Số lần sử dụng tối đa không được dưới 0',
        'used.integer'                  => 'Số lần đã sử dụng không phải là số',
        'used.min'                      => 'Số lần đã sử dụng không được dưới 0',
        'status.required'               => 'Trạng thái không được để trống',
        'status.integer'                => 'Trạng thái không phải là dạng số',
        'status.between'                => 'Trạng thái không phù hợp',
        'settings.min'                  => 'Phải có ít nhất 1 điều kiện giảm giá',
        'settings.rooms.*.distinct'     => 'Các phòng không được trùng nhau',
        'settings.rooms.*.exists'       => 'Phòng không tồn tại',
        'settings.cities.*.distinct'    => 'Các thành phố không được trùng nhau',
        'settings.cities.*.exists'      => 'Thành phố không tồn tại',
        'settings.districts.*.distinct' => 'Các quận huyện không được trùng nhau',
        'settings.districts.*.exists'   => 'Quận huyện không tồn tại',
        'settings.days.*.distinct'      => 'Các ngày áp dụng giảm giá không được trùng nhau',
        'settings.days.*.date'          => 'Các ngày áp dụng giảm giá không hợp lệ',
        'settings.days.*.after'         => 'Thời gian áp dụng cho những ngày giảm giá không được phép ở thời điểm quá khứ',
        'promotion_id.required'         => 'Vui lòng chọn chương trình giảm giá',
        'promotion_id.integer'          => 'Mã chương trình giảm giá phải là kiểu số',
        'promotion_id.exists'           => 'Chương trình giảm giá không tồn tại',

        'price_original.required' => 'Giá gốc không được để trống',
        'price_original.integer'  => 'Giá gốc phải là kiểu số',
        'price_original.min'      => 'Giá gốc không được dưới 0',
        'room_id.required'        => 'Vui lòng chọn phòng',
        'room_id.integer'         => 'Mã phòng phải là kiểu số',
        'room_id.exists'          => 'Phòng không tồn tại',
        'city_id.integer'         => 'Mã thành phố phải là kiểu số',
        'city_id.exists'          => 'Thành phố không tồn tại',
        'district_id.integer'     => 'Mã quận huyện phải là kiểu số',
        'district_id.exists'      => 'Quận huyện không tồn tại',
        'day.date'                => 'Ngày áp dụng giảm giá không hợp lệ',
        'day.after'               => 'Ngày giảm giá không được phép ở thời điểm quá khứ',
        'coupon.string'           => 'Mã giảm giá không được chứa ký tự đặc biệt',
        'coupon.min'              => 'Độ dài phải là :min',
        'coupon.exists'           => 'Mã giảm giá không tồn tại',
    ];

    protected $cp;

    /**
     * CouponController constructor.
     *
     * @param CouponLogic               $coupon
     * @param CouponRepositoryInterface|CouponRepository $cp
     */
    public function __construct(CouponLogic $coupon, CouponRepositoryInterface $cp)
    {
        $this->model = $coupon;
        $this->cp = $cp;
        $this->setTransformer(new CouponTransformer);
    }

    /**
     * Display a listing of the resource.
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function index(Request $request)
    {
        DB::enableQueryLog();
        $pageSize    = $request->get('limit', 25);
        $this->trash = $this->trashStatus($request);
        $data        = $this->model->getByQuery($request->all(), $pageSize, $this->trash);
        return $this->successResponse($data);
    }

    /**
     * Display a listing of the resource.
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function show(Request $request, $id)
    {
        try {
            $trashed = $request->has('trashed') ? true : false;
            $data    = $this->model->getById($id, $trashed);
            return $this->successResponse($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Lấy ra các Trạng thái bài viết (theo status)
     * @author sonduc <ndson1998@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function statusList()
    {
        try {
            $data = $this->simpleArrayToObject(Coupon::COUPON_STATUS);
            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Lấy ra các điều kiện giảm giá tất cả bài viết (theo status)
     * @author sonduc <ndson1998@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function allDayList()
    {
        try {
            $data = $this->simpleArrayToObject(Coupon::COUPON_ALLDAY);
            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Tính khuyến mãi của 1 booking dựa theo coupon
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDiscount(Request $request)
    {
        DB::enableQueryLog();
        try {
            // Tái cấu trúc validate để tính khuyến mãi
            $validate                   = array_only($this->validationRules, [
                'coupon',
                'price_original',
                'room_id',
                'city_id',
                'district_id',
                'day',
            ]);
            $validate['price_original'] = 'required|integer|min:0';
            $validate['city_id']        = 'integer|exists:cities,id,deleted_at,NULL';
            $validate['district_id']    = 'integer|exists:districts,id,deleted_at,NULL';
            $validate['day']            = 'date|after:yesterday';
            $this->validate($request, $validate, $this->validationMessages);
            $coupon = $this->cp->getCouponByCode($request->coupon);
            $data = [
                'data' => $this->model->checkSettingDiscount($coupon, $request->all()),
            ];

            return $this->successResponse($data, false);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollBack();
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof InvalidDateException) {
                return $this->errorResponse([
                    'errors'    => $e->getField(),
                    'exception' => $e->getValue(),
                ]);
            }
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
