<?php

namespace App\Http\Controllers\ApiCustomer;

use App\Http\Transformers\Customer\RoomTransformer;
use App\Repositories\_Customer\RoomLogic;
use App\Repositories\Rooms\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends ApiController
{
    protected $validationRules    = [

    ];
    protected $validationMessages = [

    ];

    /**
     * RoomController constructor.
     *
     * @param RoomLogic $room
     */
    public function __construct(RoomLogic $room)
    {
        $this->model = $room;
        $this->setTransformer(new RoomTransformer);
    }


    /**
     *
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            DB::enableQueryLog();
            $page_size  = $request->get('limit', 10);
            $data       = $this->model->getRooms($request->all(), $page_size);
            // dd(DB::getQueryLog());
            return $this->successResponse($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function show(Request $request, $id)
    {
        try {
            $data    = $this->model->getById($id);
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
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */

    public function getRoomSchedule($id)
    {
        DB::beginTransaction();
        DB::enableQueryLog();
        try {
            $data = [
                'data' => [
                    'blocks' => $this->model->getFutureRoomSchedule($id),
                ],
            ];
            return $this->successResponse($data, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }


    /**
     * Lấy các khoảng giờ đã khóa theo mã phòng
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getRoomScheduleByHour($id)
    {
        DB::beginTransaction();
        DB::enableQueryLog();
        try {
            $data = [
                'data' => [
                    'blocks' => $this->model->getFutureRoomScheduleByHour($id),
                ],
            ];
            return $this->successResponse($data, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function getRoomLatLong(Request $request)
    {
        try {
            $validate['lat_min']  = 'required|numeric|between:-86.00,86.00';
            $validate['lat_max']  = 'required|numeric|between:-86.00,86.00';
            $validate['long_min'] = 'required|numeric|between:-180.00,180.00';
            $validate['long_max'] = 'required|numeric|between:-180.00,180.00';
            $this->validate($request, $validate, $this->validationMessages);
            $this->trash = $this->trashStatus($request);
            $pageSize    = $request->get('limit', 25);
            $data = $this->model->getRoomLatLong($request->all(), $pageSize, $this->trash);

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
     *   đưa ra các phòng tương tự với 1 phòng nào đó
     *
     * @author sonduc <ndson1998@gmail.com>
     * @param  Request $request [description]
     * @param  [type]  $id      [description]
     * @return [type]           [description]
     */
    public function getRoomRecommend(Request $request, $id)
    {
        try {
            $pageSize    = $request->get('limit', 10);
            $data = $this->model->getRoomRecommend($pageSize, $id);

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
     * Lấy ra kiểu phòng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getRoomType()
    {
        try {
            $data = $this->simpleArrayToObject(Room::ROOM_TYPE);
            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Đếm số phòng dựa theo số sao tiêu chuẩn của phòng
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getCountRoomByStandardPoint(Request $request)
    {
        try {
            DB::enableQueryLog();
            $count          = 'standard_point';
            $data           = $this->model->getRooms($request->all(), null, $count);
            return $this->successResponseUsedForCountRoom(['data' => $data]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Lấy ra số phòng dựa theo danh sánh tiện nghi của phòng
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getCountRoomByComfortLists(Request $request)
    {
        try {
            DB::enableQueryLog();
            $count          = 'comfort_lists';
            $data           = $this->model->getRooms($request->all(), null, $count);
            //dd(DB::getQueryLog());
            return $this->successResponseUsedForCountRoom(['data' => $data]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function countNumberOfRoomByCity(Request $request)
    {
        try {
            DB::enableQueryLog();
            $limit      = $request->get('limit', 10);
            $data       = $this->model->countNumberOfRoomByCity($request->all(), $limit);

            // dd(DB::getQueryLog());
            return $this->successResponseUsedForCountRoom(['data' => $data]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function countNumberOfRoomByDistrict(Request $request)
    {
        try {
            DB::enableQueryLog();
            $limit      = $request->get('limit', 10);
            $data       = $this->model->countNumberOfRoomByDistrict($request->all(), $limit);

            // dd(DB::getQueryLog());
            return $this->successResponseUsedForCountRoom(['data' => $data]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Lấy kiểu thuê phòng
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function roomRentType()
    {
        try {
            $data = $this->simpleArrayToObject(Room::ROOM_RENT_TYPE);
            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }
}
