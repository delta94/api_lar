<?php

namespace App\Http\Transformers;

use App\Helpers\ErrorCore;
use App\Repositories\Rooms\RoomMedia;
use League\Fractal\TransformerAbstract;

class RoomMediaTransformer extends TransformerAbstract
{
    protected $availableIncludes
        = [

        ];

    /**
     *
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param RoomMedia|null $room
     *
     * @return array
     */
    public function transform(RoomMedia $room = null)
    {
        if (is_null($room)) {
            return [];
        }

        return [
            'id'         => $room->id,
            'room_id'    => $room->room_id,
            'image'      => $room->image,
            'type'       => $room->type,
            'type_txt'   => $room->roomMedia(),
            'status'     => $room->status,
            'status_txt' => $room->status == 1 ? trans2('status.activate') : trans2('status.deactivate'),
            'created_at' => $room->created_at ? $room->created_at->format('Y-m-d H:m:i') : trans2(ErrorCore::UNDEFINED),
            'updated_at' => $room->updated_at ? $room->updated_at->format('Y-m-d H:m:i') : trans2(ErrorCore::UNDEFINED),
        ];
    }

}
