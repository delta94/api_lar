<?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 10/12/2018
 * Time: 15:04
 */

namespace App\Http\Controllers\ApiCustomer;


use App\Http\Transformers\PaymentHistoryTransformer;
use App\Repositories\Bookings\BookingConstant;
use App\Repositories\Bookings\BookingRepositoryInterface;
use App\Repositories\Payments\PaymentHistoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\BookingEvent;

class PaymentHistoryController extends ApiController
{
    protected $booking;
    /**
     * PaymentHistoryController constructor.
     *
     * @param PaymentHistoryRepository $payment
     */
    public function __construct(PaymentHistoryRepositoryInterface $payment,BookingRepositoryInterface $booking)
    {
        $this->model   = $payment;
        $this->booking = $booking;
        $this->setTransformer(new PaymentHistoryTransformer());
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View|string
     */
    public function success(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request['transaction_status'] == 4 || $request['transaction_status'] == 13) {
                // Lấy thông tin booking theo mã code nhận được từ bảo kim trả về
                $booking = $this->booking->getBookingByCode($request['order_id'])->toArray();
                $payment_history = [
                     'money_received' => $request['net_amount'],
                     'note'           => $booking['payment_method'] == BookingConstant:: BAOKIM? 'Xác nhận đã thanh toán booking mã: '. $request['order_id'] . 'từ Bảo Kim' : 'Thanh toán booking: ' . $request['order_id'] . 'qua ATM nội địa',
                     'confirm'        => BookingConstant::CONFIRM,
                ];

               // Cập nhật trạng thái đã thanh toán cho booking.
                $booking['payment_status'] = BookingConstant::PAID;
                $booking['status']         = BookingConstant::BOOKING_CONFIRM;
                $booking = $this->booking->update($booking['id'],$booking);
                // Cập nhât lịch sử giao dich.
                $data    = $this->model->storePaymentHistory($booking, $payment_history);
                 DB::commit();
                 logs('payment_history', 'đã thêm thanh toán cho booking mã ' . $booking->code, $data);
                  event(new BookingEvent($booking));

                return response()->json(['message' => 'Cám ơn bạn đã sử dụng dich vụ của WESTAY']);
           }

        } catch (\Exception $exception) {
            DB::rollback();
            return $exception->getMessage();
        }
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($code)
    {
        $response = [
            'code'    => $code,
            'status'  => 'error',
            'message' => 'Thanh toán thất bại',
        ];
        logs('payment_history', 'Thanh toán thất bại' . $code);
        return response()->json($response);
    }

}