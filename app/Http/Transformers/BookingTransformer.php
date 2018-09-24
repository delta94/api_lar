<?php

namespace App\Http\Transformers;

use App\Http\Transformers\Traits\FilterTrait;
use App\Repositories\Bookings\Booking;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class BookingTransformer extends TransformerAbstract
{
    use FilterTrait;
    protected $availableIncludes
        = [
            'customer', 'merchant', 'booking_status', 'payments', 'room', 'city', 'district',
        ];

    public function transform(Booking $booking = null)
    {
        if (is_null($booking)) {
            return [];
        }

        return [
            'id'                 => $booking->id,
            'uuid'               => $booking->uuid,
            'code'               => $booking->code,
            'name'               => $booking->name,
            'sex'                => $booking->sex,
            'sex_txt'            => $booking->getSex(),
            'birthday'           => $booking->birthday,
            'phone'              => $booking->phone,
            'email'              => $booking->email,
            'email_received'     => $booking->email_received,
            'name_received'      => $booking->name_received,
            'phone_received'     => $booking->phone_received,
            'room_id'            => $booking->room_id,
            'customer_id'        => $booking->customer_id,
            'merchant_id'        => $booking->merchant_id,
            'checkin'            => $booking->checkin ? date('Y-m-d H:i:s', $booking->checkin) : 'Không xác định',
            'checkout'           => $booking->checkout ? date('Y-m-d H:i:s', $booking->checkout) : 'Không xác định',
            'number_of_guests'   => $booking->number_of_guest ?? 0,
            'price_original'     => $booking->price_original,
            'price_discount'     => $booking->price_discount,
            'booking_fee'        => $booking->booking_fee,
            'coupon'             => $booking->coupon,
            'coupon_txt'         => $booking->coupon ?? 'Không áp dụng coupon',
            'note'               => $booking->note,
            'service_fee'        => $booking->service_fee,
            'total_fee'          => $booking->total_fee,
            'booking_type'       => $booking->booking_type,
            'booking_type_txt'   => $booking->getBookingType(),
            'type'               => $booking->type,
            'type_txt'           => $booking->getType(),
            'source'             => $booking->source,
            'source_txt'         => $booking->getBookingSource(),
            'payment_status'     => $booking->payment_status,
            'payment_status_txt' => $booking->getPaymentStatus(),
            'status'             => $booking->status,
            'status_txt'         => $booking->getBookingStatus(),
            'price_range'        => $booking->price_range,
            'price_range_txt'    => $booking->getPriceRange(),
            'exchange_rate'      => $booking->exchange_rate,
            'created_at'         => $booking->created_at->format('Y-m-d H:i:s'),
            'updated_at'         => $booking->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeCustomer(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        return $this->item($booking->customer, new UserTransformer);
    }

    public function includeMerchant(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        return $this->item($booking->merchant, new UserTransformer);
    }

    public function includeBookingStatus(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        return $this->item($booking->bookingStatus, new BookingStatusTransformer);
    }

    public function includePayments(Booking $booking = null, ParamBag $params = null)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        $data = $this->limitAndOrder($params, $booking->payments())->get();

        return $this->collection($data, new PaymentHistoryTransformer);
    }

    public function includeRoom(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        try {
            if (is_null($booking->room)) throw new \Exception;
            return $this->item($booking->room, new RoomTransformer);
        } catch (\Exception $e) {
            return $this->primitive(null);
        }
    }

    public function includeCity(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        try {
            if (is_null($booking->room->city)) throw new \Exception;
            return $this->item($booking->room->city, new CityTransformer);
        } catch (\Exception $e) {
            return $this->primitive(null);
        }
    }

    public function includeDistrict(Booking $booking)
    {
        if (is_null($booking)) {
            return $this->null();
        }

        try {
            if (is_null($booking->room->district)) throw new \Exception;
            return $this->item($booking->room->district, new DistrictTransformer);
        } catch (\Exception $e) {
            return $this->primitive(null);
        }
    }
}
