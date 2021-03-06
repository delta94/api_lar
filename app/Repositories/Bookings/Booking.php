<?php

namespace App\Repositories\Bookings;

use App\Repositories\Entity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Entity
{
    use PresentationTrait, FilterTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid', 'code', 'name', 'phone', 'email', 'sex', 'birthday', 'email_received', 'name_received', 'phone_received', 'room_id', 'customer_id', 'merchant_id', 'checkin', 'checkout', 'price_original', 'additional_fee', 'price_discount', 'coupon', 'coupon_discount', 'note', 'total_fee',
        'status', 'email_reminder', 'email_reviews', 'number_of_guests', 'service_fee', 'type', 'booking_type', 'payment_method', 'payment_status', 'price_range', 'source', 'exchange_rate', 'total_refund', 'settings', 'age_range','host_reminder','review_url','status_reviews','status_host_reviews'
    ];

    /**
     * The attributes that are cast permission from json string to array
     * @var array
     */
    protected $casts = ['permissions' => 'array'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            $model->uuid = hashid_encode($model->id);
            $model->code = strtoupper(BookingConstant::PREFIX . hashid_encode($model->id));
            $model->save();
        });
    }

    public function customer()
    {
        return $this->belongsTo(\App\User::class, 'customer_id');
    }

    public function merchant()
    {
        return $this->belongsTo(\App\User::class, 'merchant_id');
    }

    public function bookingStatus()
    {
        return $this->hasOne(BookingStatus::class, 'booking_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Repositories\Payments\PaymentHistory::class, 'booking_id');
    }

    public function room()
    {
        return $this->belongsTo(\App\Repositories\Rooms\Room::class, 'room_id');
    }

    public function cancel()
    {
        return $this->hasMany(BookingCancel::class, 'booking_id');
    }

    /**
     *
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reviews()
    {
        return $this->hasOne(\App\Repositories\Rooms\RoomReview::class, 'booking_id');
    }

    
    public function transactions()
    {
        return $this->hasMany(\App\Repositories\Transactions\Transaction::class, 'booking_id');
    }
}
