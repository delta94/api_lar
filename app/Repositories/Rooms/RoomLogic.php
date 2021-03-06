<?php

namespace App\Repositories\Rooms;

use App\Repositories\BaseLogic;
use App\Repositories\Bookings\BookingRepository;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\Repositories\Users\UserRepository;
use App\Repositories\Users\UserRepositoryInterface;
use App\Events\GenerateWestayRoomCalendarEvent;

class RoomLogic extends BaseLogic
{
    use RoomLogicTrait;
    /**
     * Room model.
     * @var Room
     */
    protected $roomTranslate;
    protected $roomOptionalPrice;
    protected $roomMedia;
    protected $roomTimeBlock;
    protected $booking;
    protected $roomReview;
    protected $user;

    /**
     * RoomLogic constructor.
     *
     * @param RoomRepositoryInterface|RoomRepository                           $room
     * @param RoomTranslateRepositoryInterface|RoomTranslateRepository         $roomTranslate
     * @param RoomOptionalPriceRepositoryInterface|RoomOptionalPriceRepository $roomOptionalPrice
     * @param RoomMediaRepositoryInterface|RoomMediaRepository                 $roomMedia
     * @param RoomTimeBlockRepositoryInterface|RoomTimeBlockRepository         $roomTimeBlock
     * @param BookingRepositoryInterface|BookingRepository                     $booking
     * @param RoomReviewRepositoryInterface|RoomReviewRepository               $roomReview
     * @param UserRepositoryInterface|UserRepository                           $user
     */
    public function __construct(
        RoomRepositoryInterface $room,
        RoomTranslateRepositoryInterface $roomTranslate,
        RoomOptionalPriceRepositoryInterface $roomOptionalPrice,
        RoomMediaRepositoryInterface $roomMedia,
        RoomTimeBlockRepositoryInterface $roomTimeBlock,
        BookingRepositoryInterface $booking,
        RoomReviewRepositoryInterface $roomReview,
        UserRepositoryInterface $user
    ) {
        $this->model             = $room;
        $this->roomTranslate     = $roomTranslate;
        $this->roomOptionalPrice = $roomOptionalPrice;
        $this->roomMedia         = $roomMedia;
        $this->roomTimeBlock     = $roomTimeBlock;
        $this->booking           = $booking;
        $this->roomReview        = $roomReview;
        $this->user              = $user;
    }
    /**
     * Lưu trữ bản ghi của phòng vào bảng rooms, room_translates, room_optional_prices, room_comfort
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param array $data
     *
     * @return \App\Repositories\Eloquent
     */
    public function store($data, $room = [])
    {
        $data['settings'] = $this->model->checkValidRefund($data);
        $data_room        = parent::store($data);
        $this->roomTranslate->storeRoomTranslate($data_room, $data);
        $this->storeRoomComforts($data_room, $data);
        $this->roomOptionalPrice->storeRoomOptionalPrice($data_room, $data);
        $this->roomMedia->storeRoomMedia($data_room, $data);
        event(new GenerateWestayRoomCalendarEvent($data_room));
        // $this->roomTimeBlock->storeRoomTimeBlock($data_room, $data);

        return $data_room;
    }

    /**
     * Lưu comforts cho phòng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $data_room
     * @param $data
     */
    public function storeRoomComforts($data_room, $data)
    {
        if (!empty($data)) {
            if (isset($data['comforts'])) {
                $data_room->comforts()->sync($data['comforts']);
            }
        }
    }

    /**
     * Cập nhật cho phòng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param int   $id
     * @param       $data
     * @param array $excepts
     * @param array $only
     *
     * @return \App\Repositories\Eloquent
     */
    public function update($id, $data, $excepts = [], $only = [])
    {
        $data['settings']= $this->model->checkValidRefund($data);
        // dd($data['settings']);
        $data_room = parent::update($id, $data);
        $this->roomTranslate->updateRoomTranslate($data_room, $data);
        $this->roomOptionalPrice->updateRoomOptionalPrice($data_room, $data);
        $this->roomMedia->updateRoomMedia($data_room, $data);
        // $this->roomTimeBlock->updateRoomTimeBlock($data_room, $data);
        $this->storeRoomComforts($data_room, $data);

        return $data_room;
    }

    /**
     * Cập nhật riêng lẻ các thuộc tính của phòng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param       $id
     * @param array $data
     *
     * @return \App\Repositories\Eloquent
     */


    /**
     * Lấy ra những ngày không hợp lệ
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $id
     *
     * @return array
     */
    public function getFutureRoomSchedule($id)
    {
        $room = parent::getById($id);
        return $this->getBlockedScheduleByRoomId($room->id);
    }

    /**
     * Lấy ra những khoảng giờ gị khóa
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     *
     * @return array
     */
    public function getFutureRoomScheduleByHour($id)
    {
        $room = parent::getById($id);
        return $this->getBlockedScheduleByHour($room->id);
    }



    /**
     * Tính toán rating trung bình cho từng phòng
     * @author tuananh1402 <tuananhpham1402@gmail.com>
     *
     * @param  $params
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function ratingCalculate($room_id, $reviews)
    {
        \DB::enableQueryLog();
        $room = $this->model->getById($room_id)->with('reviews')->first();
        $denominator    = sizeof($room['reviews']) !== 0 ? sizeof($room['reviews']) : 1;

        $current_cleanliness    = $room->avg_cleanliness * $denominator;
        $current_service        = $room->avg_service * $denominator;
        $current_quality        = $room->avg_quality * $denominator;
        $current_avg_rating     = $room->avg_avg_rating * $denominator;
        $current_valuable       = $room->avg_valuable * $denominator;
        $current_recommend      = $room->total_recommend;

        // foreach ($reviews as $key => $value) {
        $current_cleanliness    += $reviews->cleanliness;
        $current_service        += $reviews->service;
        $current_quality        += $reviews->quality;
        $current_avg_rating     += $reviews->avg_rating;
        $current_valuable       += $reviews->valuable;
        $current_recommend      += $reviews->recommend;
        // }
        \DB::beginTransaction();
        try {
            $room->update([
                'avg_cleanliness'   => round(($current_cleanliness / $denominator), 2),
                'avg_service'       => round(($current_service / $denominator), 2),
                'avg_quality'       => round(($current_quality / $denominator), 2),
                'avg_avg_rating'    => round(($current_avg_rating / $denominator), 2),
                'avg_valuable'      => round(($current_valuable / $denominator), 2),
                'total_review'      => $denominator + 1,
                'total_recommend'   => $current_recommend
            ]);
            \DB::commit();
        } catch (\Throwable $t) {
            \DB::rollback();
            throw $t;
        }
    }

    public function getRoomLatLong($data, $size)
    {
        return $this->model->getRoomLatLong($data, $size);
    }

    public function getRoomRecommend($size, $id)
    {
        return $this->model->getRoomRecommend($size, $id);
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     * @param array $data
     * @return \App\Repositories\Eloquent
     * @throws \Exception
     */
    public function minorRoomUpdate($id, $data = [])
    {
        return parent::update($id, $data);
    }

    public function updateComission($data = [])
    {
        $this->model->updateComission($data);
    }

    public function generateWestayRoomCalendar($room)
    {
        // $rooms = $this->model->get();
        $room            = $this->model->getById($room->id);
        $created_at      = $room->created_at;
        $hash_string     = $room->room_type . $room->merchant_id;
        $hashed_string   = hash_hmac('sha256', $hash_string, $created_at);
        $dummy_text      = substr(strtoupper($hashed_string), 0, 50);
        $room->westay_calendar = env('API_URL_MERCHANT').'get-calendar/'.$room->id.'?'.$dummy_text;
        $room->save();
    }
}
