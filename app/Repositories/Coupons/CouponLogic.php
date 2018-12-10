<?php

namespace App\Repositories\Coupons;

use App\Helpers\GlobalTrans;
use App\Repositories\BaseLogic;
use App\Repositories\Bookings\BookingConstant;
use App\Repositories\Cities\CityRepository;
use App\Repositories\Cities\CityRepositoryInterface;
use App\Repositories\Districts\DistrictRepository;
use App\Repositories\Districts\DistrictRepositoryInterface;
use App\Repositories\Rooms\Room;
use App\Repositories\Rooms\RoomOptionalPriceRepository;
use App\Repositories\Rooms\RoomOptionalPriceRepositoryInterface;
use App\Repositories\Rooms\RoomRepository;
use App\Repositories\Rooms\RoomRepositoryInterface;
use App\Repositories\Rooms\RoomTranslateRepository;
use App\Repositories\Rooms\RoomTranslateRepositoryInterface;
use App\Repositories\Users\UserRepository;
use App\Repositories\Users\UserRepositoryInterface;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CouponLogic extends BaseLogic
{
    use CouponLogicTrait;

    protected $model;
    protected $room;
    protected $room_translate;
    protected $city;
    protected $district;
    protected $user;
    protected $op;
    protected $cp;

    /**
     * CouponLogic constructor.
     *
     * @param CouponRepositoryInterface|CouponRepository                       $coupon
     * @param RoomTranslateRepositoryInterface|RoomTranslateRepository         $room_translate
     * @param RoomRepositoryInterface|RoomRepository                           $room
     * @param CityRepositoryInterface|CityRepository                           $city
     * @param DistrictRepositoryInterface|DistrictRepository                   $district
     * @param UserRepositoryInterface|UserRepository                           $user
     * @param RoomOptionalPriceRepositoryInterface|RoomOptionalPriceRepository $op
     * @param CouponRepositoryInterface|CouponRepository                       $cp
     */
    public function __construct(
        CouponRepositoryInterface $coupon,
        RoomTranslateRepositoryInterface $room_translate,
        RoomRepositoryInterface $room,
        CityRepositoryInterface $city,
        DistrictRepositoryInterface $district,
        UserRepositoryInterface $user,
        RoomOptionalPriceRepositoryInterface $op,
        CouponRepositoryInterface $cp
    )
    {
        $this->model          = $coupon;
        $this->room           = $room;
        $this->room_translate = $room_translate;
        $this->city           = $city;
        $this->district       = $district;
        $this->user           = $user;
        $this->op             = $op;
        $this->cp             = $cp;
    }

    /**
     * Thêm mới dữ liệu vào coupon
     * @author sonduc <ndson1998@gmail.com>
     *
     * @param array $data
     *
     * @return \App\Repositories\Eloquent
     */
    public function store($data)
    {
        $data['code'] = strtoupper($data['code']);
        if (empty($data['settings']['min_price'])) {
            $data['settings']['min_price'] = 0;
        };
        $data['settings'] = json_encode($data['settings']);
        $data_coupon      = parent::store($data);
        return $data_coupon;
    }

    /**
     * Cập nhật dữ liệu cho promotion
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param       $id
     * @param       $data
     * @param array $excepts
     * @param array $only
     *
     * @return \App\Repositories\Eloquent
     * @throws \Exception
     */
    public function update($id, $data, $excepts = [], $only = [])
    {
        $coupon = $this->model->getById($id);
        if ($coupon->used > 0) {
            throw new \Exception('Không có quyền sửa đổi mục này');
        };

        $data['code'] = strtoupper($data['code']);

        if (empty($data['settings']['min_price'])) {
            $data['settings']['min_price'] = 0;
        };

        $data['settings'] = json_encode($data['settings']);
        $data_coupon      = parent::update($id, $data);
        return $data_coupon;
    }

    /**
     * Cập nhật trường trạng thái status
     * @author sonduc <ndson1998@gmail.com>
     *
     * @param $id
     * @param $data
     *
     * @return \App\Repositories\Eloquent
     */
    public function singleUpdate($id, $data)
    {
        $data_coupon = parent::update($id, $data);
        return $data_coupon;
    }

    /**
     * Chuyển đổi json setting sang mảng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $scope
     * @param $pageSize
     * @param $trash
     * @param $coupon
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    public function transformListCoupon($scope, $pageSize, $trash, $coupon)
    {
        $list_room_id = $list_city_id = $list_district_id = $list_merchant_id = $list_user_id = [];

        $data = !$coupon ? $this->model->getByQuery($scope, $pageSize, $trash) : [$coupon];

        if (\count($data)) {
            foreach ($data as $key => $value) {
                $settings         = json_decode($value->settings);
                $list_room_id     = array_merge(($settings->rooms ?? []), $list_room_id);
                $list_city_id     = array_merge(($settings->cities ?? []), $list_city_id);
                $list_district_id = array_merge(($settings->districts ?? []), $list_district_id);
                $list_merchant_id = array_merge(($settings->merchants ?? []), $list_merchant_id);
                $list_user_id     = array_merge(($settings->user ?? []), $list_user_id);
            }

            $arrData = $this->transformCouponIndex(
                $list_room_id, $list_city_id, $list_district_id,
                (!empty($settings->days) ? $settings->days : []),
                $list_merchant_id, $list_user_id, $data);

            return \count($data) === 1 ? $arrData[0] : $arrData;
        }
        return $data;
    }

    /**
     * Chuyển đổi json setting sang mảng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param array $rooms
     * @param array $cities
     * @param array $districts
     * @param array $days
     * @param array $merchants
     * @param array $users
     * @param array $coupons
     *
     * @return array
     */
    public function transformCouponIndex($rooms = [], $cities = [], $districts = [], $days = [], $merchants = [], $users = [], $coupons = [])
    {
        $arrRoom        = $this->room_translate->getRoomByListIdIndex($rooms);
        $arrCity        = $this->city->getCityByListIdIndex($cities);
        $arrDistrict    = $this->district->getDistrictByListIdIndex($districts);
        $arrMerchants   = $this->user->getUserByListIdIndex($merchants, User::IS_OWNER);
        $arrUsers       = $this->user->getUserByListIdIndex($users, User::NOT_OWNER);
        $arrRoomType    = arrayToObject(Room::ROOM_TYPE);
        $arrBookingType = arrayToObject(BookingConstant::BOOKING_TYPE);
        $arrDay         = $days;
        $arrDate        = [
            [
                'id'   => Carbon::MONDAY + 1,
                'name' => trans2(GlobalTrans::MONDAY),
            ],
            [
                'id'   => Carbon::TUESDAY + 1,
                'name' => trans2(GlobalTrans::TUESDAY),
            ],
            [
                'id'   => Carbon::WEDNESDAY + 1,
                'name' => trans2(GlobalTrans::WEDNESDAY),
            ],
            [
                'id'   => Carbon::THURSDAY + 1,
                'name' => trans2(GlobalTrans::THURSDAY),
            ],
            [
                'id'   => Carbon::FRIDAY + 1,
                'name' => trans2(GlobalTrans::FRIDAY),
            ],
            [
                'id'   => Carbon::SATURDAY + 1,
                'name' => trans2(GlobalTrans::SATURDAY),
            ],
            [
                'id'   => Carbon::SUNDAY + 1,
                'name' => trans2(GlobalTrans::SUNDAY),
            ],
        ];

        foreach ($coupons as $key => $value) {
            $settings  = json_decode($value->settings);
            $bind      = $settings->bind ?? [];
            $list_stay = $list_create = [];

            $arrRoom_filter        = $this->arrayFilterAndFlatten($arrRoom, $settings, 'rooms');
            $arrCity_filter        = $this->arrayFilterAndFlatten($arrCity, $settings, 'cities');
            $arrDistrict_filter    = $this->arrayFilterAndFlatten($arrDistrict, $settings, 'districts');
            $arrBookingType_filter = $this->arrayFilterAndFlatten($arrBookingType, $settings, 'booking_type');
            $arrMerchant_filter    = $this->arrayFilterAndFlatten($arrMerchants, $settings, 'merchants');
            $arrUser_filter        = $this->arrayFilterAndFlatten($arrUsers, $settings, 'users');
            $arrDate_filter        = $this->arrayFilterAndFlatten($arrDate, $settings, 'days_of_week');
            $arrRoomType_filter    = $this->arrayFilterAndFlatten($arrRoomType, $settings, 'room_type');


            if (!empty($settings->booking_stay)) {
                $dataBookingStay = $settings->booking_stay;

                $start_stay  = Carbon::parse($dataBookingStay[0]);
                $end_stay    = Carbon::parse($dataBookingStay[1]);
                $period_stay = CarbonPeriod::between($start_stay, $end_stay);

                foreach ($period_stay as $day) {
                    /** @var Carbon $day */
                    $list_stay[] = $day->toDateString();
                }
            }

            if (!empty($settings->booking_create)) {
                $dataBookingCreate = $settings->booking_create;

                $start_create  = Carbon::parse($dataBookingCreate[0]);
                $end_create    = Carbon::parse($dataBookingCreate[1]);
                $period_create = CarbonPeriod::between($start_create, $end_create);
                foreach ($period_create as $day) {
                    $list_create[] = $day->toDateString();
                }
            }

            $arrBookingCreate_filter = $list_create;
            $arrBookingStay_filter   = $list_stay;


            $arrMinPrice_filter = !empty($settings->min_price) ? $settings->min_price : 0;

            $arrayTransformSetting   = [
                'bind'           => $bind,
                'rooms'          => $arrRoom_filter,
                'cities'         => $arrCity_filter,
                'districts'      => $arrDistrict_filter,
                'days'           => $arrDay,
                'booking_type'   => $arrBookingType_filter,
                'booking_create' => $arrBookingCreate_filter,
                'booking_stay'   => $arrBookingStay_filter,
                'merchants'      => $arrMerchant_filter,
                'users'          => $arrUser_filter,
                'days_of_week'   => $arrDate_filter,
                'room_type'      => $arrRoomType_filter,
                'min_price'      => $arrMinPrice_filter,
            ];
            $coupons[$key]->settings = json_encode($arrayTransformSetting);
        }

        return $coupons;
    }

    /**
     * Filter array
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $original
     * @param $settings
     * @param $prop
     *
     * @return array
     */
    private function arrayFilterAndFlatten($original, $settings, $prop)
    {
        $haystack = (is_array($settings->$prop) ? $settings->$prop : [$settings->$prop]) ?? [];

        return array_values(
            array_filter($original, function ($item) use ($haystack) {
                return in_array($item['id'], $haystack);
            })
        );
    }

    /**
     * Cập nhật số lần dùng cho 1 coupon
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $code
     *
     * @return \App\Repositories\Eloquent
     * @throws \Exception
     */
    public function updateUsable($code)
    {
        $coupon = $this->cp->getCouponByCode(strtoupper($code));
        if ($coupon) {
            $id     = $coupon['id'];
            $coupon = (array)json_decode($coupon);

            if ($coupon['usable'] > 0) {
                $coupon['usable']--;
                $coupon['used']++;
                $data_coupon = parent::update($id, $coupon);
                return $data_coupon;
            }

            throw new \Exception('Mã khuyến mãi đã hết số lần sử dụng');
        }
    }

}
