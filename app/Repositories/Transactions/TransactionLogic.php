<?php

namespace App\Repositories\Transactions;

use App\Repositories\BaseLogic;
use App\Repositories\BookingRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\Rooms\RoomRepositoryInterface;
use App\Repositories\TransactionTypes\TransactionType;
use App\Repositories\Bookings\BookingConstant;

class TransactionLogic extends BaseLogic
{
    use TransactionLogicTrait;


    public function __construct(
        TransactionRepositoryInterface $transaction,
        BookingRepositoryInterface $booking,
        UserRepositoryInterface $user,
        RoomRepositoryInterface $room
    ) {
        $this->model    = $transaction;
        $this->booking  = $booking;
        $this->user     = $user;
        $this->room     = $room;
    }

    public function createBookingTransaction($dataBooking)
    {
        $dataBooking = $dataBooking->data;

        $room_id     = $dataBooking['room_id'];
        $merchant_id = $dataBooking['merchant_id'];
        $date        = Carbon::parse($dataBooking['created_at'])->toDateString();
        $booking_id  = $dataBooking['id'];
        // $commission   = $this->room->getRoomCommission($room_id);
        $commission  = 20;
        $type        = TransactionType::TRANSACTION_BOOKING;
        $credit      = $dataBooking['total_fee'];
        $bonus       = 0;

        $credit = ($dataBooking['status'] == BookingConstant::BOOKING_CANCEL) ? ($dataBooking['total_fee'] - $dataBooking['total_refund']) : 0;

        $debit = ($dataBooking['status'] == BookingConstant::BOOKING_NEW || $dataBooking['status'] == BookingConstant::BOOKING_CONFIRM) ? $dataBooking['total_fee'] : 0;

        $dataTransaction = [
            'type'          => $type,
            'date_create'   => $date,
            'user_id'       => $merchant_id,
            'room_id'       => $room_id,
            'booking_id'    => $booking_id,
            'credit'        => (int) ceil($credit),
            'debit'         => (int) ceil($debit),
            'bonus'         => $bonus,
            'commission'    => $commission
        ];
        
        return parent::store($dataTransaction);
    }
}
