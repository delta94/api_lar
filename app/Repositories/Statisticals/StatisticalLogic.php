<?php

namespace App\Repositories\Statisticals;

use App\Repositories\BaseLogic;
use App\Repositories\Bookings\BookingRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StatisticalLogic extends BaseLogic
{
    protected $model;
    protected $booking;

    public function __construct(
        StatisticalRepositoryInterface $statistical,
        BookingRepositoryInterface $booking) 
    {
        $this->model   = $statistical;
        $this->booking = $booking;
    }

    public function bookingStatistical($data)
    {
        if (isset($data['date_start']) == false) {
            $data['date_start'] = Carbon::now()->startOfMonth()->toDateTimeString();
        }
        if (isset($data['date_end']) == false) {
            $data['date_end'] = Carbon::now()->toDateTimeString();
        }
        switch ($data['view']) {
            case 'day':
                $booking = $this->booking->countBookingDay($data['date_start'],$data['date_end']);
                break;

            case 'week':
                $booking = $this->booking->countBookingWeek($data['date_start'],$data['date_end']);
                break;

            case 'month':
                $booking = $this->booking->countBookingMonth($data['date_start'],$data['date_end']);
                break;

            case 'year':
                $booking = $this->booking->countBookingYear($data['date_start'],$data['date_end']);
                break;
            default:
                $booking = $this->booking->countBookingWeek($data['date_start'],$data['date_end']);
                break;
        }

        return $booking;
    }

    public function statisticalCity($data)
    {
        if (isset($data['date_start']) == false) {
            $data['date_start'] = Carbon::now()->startOfMonth()->toDateTimeString();
        }
        if (isset($data['date_end']) == false) {
            $data['date_end'] = Carbon::now()->toDateTimeString();
        }
        switch ($data['view']) {
            case 'day':
                $booking = $this->booking->countBookingCityDay($data['date_start'],$data['date_end']);
                break;

            case 'week':
                $booking = $this->booking->countBookingCityWeek($data['date_start'],$data['date_end']);
                break;

            case 'month':
                $booking = $this->booking->countBookingCityMonth($data['date_start'],$data['date_end']);
                break;

            case 'year':
                $booking = $this->booking->countBookingCityYear($data['date_start'],$data['date_end']);
                break;
            default:
                $booking = $this->booking->countBookingCityWeek($data['date_start'],$data['date_end']);
                break;
        }

        return $booking;
    }

    public function statisticalDistrict($data)
    {
        if (isset($data['date_start']) == false) {
            $data['date_start'] = Carbon::now()->startOfMonth()->toDateTimeString();
        }
        if (isset($data['date_end']) == false) {
            $data['date_end'] = Carbon::now()->toDateTimeString();
        }
        switch ($data['view']) {
            case 'day':
                $booking = $this->booking->countBookingDistrictDay($data['date_start'],$data['date_end']);
                break;

            case 'week':
                $booking = $this->booking->countBookingDistrictWeek($data['date_start'],$data['date_end']);
                break;

            case 'month':
                $booking = $this->booking->countBookingDistrictMonth($data['date_start'],$data['date_end']);
                break;

            case 'year':
                $booking = $this->booking->countBookingDistrictYear($data['date_start'],$data['date_end']);
                break;
            default:
                $booking = $this->booking->countBookingDistrictWeek($data['date_start'],$data['date_end']);
                break;
        }

        return $booking;
    }

    public function statisticalBookingType($data)
    {
        if (isset($data['date_start']) == false) {
            $data['date_start'] = Carbon::now()->startOfMonth()->toDateTimeString();
        }
        if (isset($data['date_end']) == false) {
            $data['date_end'] = Carbon::now()->toDateTimeString();
        }
        switch ($data['view']) {
            case 'day':
                $booking = $this->booking->countBookingTypeDay($data['date_start'],$data['date_end']);
                break;

            case 'week':
                $booking = $this->booking->countBookingTypeWeek($data['date_start'],$data['date_end']);
                break;

            case 'month':
                $booking = $this->booking->countBookingTypeMonth($data['date_start'],$data['date_end']);
                break;

            case 'year':
                $booking = $this->booking->countBookingTypeYear($data['date_start'],$data['date_end']);
                break;
            default:
                $booking = $this->booking->countBookingTypeWeek($data['date_start'],$data['date_end']);
                break;
        }

        return $booking;
    }
}
