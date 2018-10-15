<?php

namespace App\Repositories\Rooms;

use App\Repositories\GlobalTrait;

trait FilterTrait
{
    use GlobalTrait;

    public function scopeName($query, $q)
    {
        if ($q) {
            $roomColumns      = $this->columnsConverter(['id', 'created_at', 'updated_at']);
            $roomTransColumns = $this->columnsConverter(['name'], 'room_translates', false);
            $columns          = self::mergeUnique($roomColumns, $roomTransColumns);

            $query
                ->addSelect($columns)
                ->join('room_translates', 'rooms.id', '=', 'room_translates.room_id')
                ->where('room_translates.name', 'like', "%${q}%");
        }
        return $query;
    }

    /**
     * Scope City
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeCity($query, $q)
    {
        if ($q) {
            $query->where('rooms.city_id', $q);
        }

        return $query;
    }

    /**
     * Scope District
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeDistrict($query, $q)
    {
        if ($q) {
            $query->where('rooms.district_id', $q);
        }

        return $query;
    }

    /**
     * Scope Merchant
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeMerchant($query, $q)
    {
        if ($q) {
            $query->where('rooms.merchant_id', $q);
        }

        return $query;
    }

    /**
     * Scope Room Status
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeStatus($query, $q)
    {
        if (array_key_exists($q, $this::ROOM_STATUS)) {
            $query->where('rooms.status', $q);
        }

        return $query;
    }

    /**
     * Scope Manager
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeManager($query, $q)
    {
        if (is_numeric($q) && $q == $this::MANAGER_DEACTIVE) {
            return $query->where('rooms.is_manager', $q);
        }

        return $query->where('rooms.is_manager', $this::MANAGER_ACTIVE);
    }

    /**
     * Kiểu cho thuê phòng (theo ngày , theo giờ, cả ngày và giờ)
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeRentType($query, $q)
    {
        if ($q && is_numeric($q)&& array_key_exists($q, $this::ROOM_RENT_TYPE)) {
            return $query->where('rooms.rent_type', $q);
        }

        return $query;
    }

    /**
     * Scope latest_deal
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeLatestDeal($query, $q)
    {
        if ($q && is_numeric($q))
        {
            return $query->where('rooms.latest_deal', $q);
        }
    }

    /**
     * Kiểu phòng (theo căn hộ ,nhà riêng, phòng riêng)
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $query
     * @param $q
     *
     * @return mixed
     */
    public function scopeTypeRoom($query, $q)
    {
        if ($q && is_numeric($q)&& array_key_exists($q, $this::ROOM_TYPE)) {
            return $query->where('rooms.room_type', $q);
        }

        return $query;
    }

    /**
     * Lọc phòng theo khoảng giá dựa theo bảng room
     * @author 0ducchien612 <0ducchien612@gmail.com>
     *
     * @param       $room
     * @param array $data
     * @param array $list
     *
     * @return array
     */

    public function scopePriceRangeStart($query,$q)
    {
        if ($q)
        {
            $query->where('rooms.price_hour','>=',$q)->orWhere('rooms.price_day','>=',$q);
        }
        return $query;

    }

    public function scopePriceRangeEnd($query, $q)
    {
        if ($q)
        {
            $query->where('rooms.price_hour','<=',$q)->where('rooms.price_day','<=',$q);
        }
        return $query;

    }

    /**
     * Lọc phòng theo khoảng giá dựa theo bảng room
     * @author 0ducchien612 <0ducchien612@gmail.com>
     *
     * @param       $room
     * @param array $data
     * @param array $list
     *
     * @return array
     */

}
