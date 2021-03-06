<?php

/*
|--------------------------------------------------------------------------
| Common api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/bookings/booking-type-list', 'BookingController@bookingTypeList');
$router->get('/bookings/cancel-reason-list', 'BookingController@bookingCancelList');
$router->group([
    'middleware' => 'auth',
], function ($router) {
    /**
     * Booking-customer.
     */
    $router->get('/bookings', 'BookingController@index');
    $router->get('/bookings/{id}', 'BookingController@show');
    $router->post('/bookings/cancel-booking/{id}', 'BookingController@cancelBooking');

    /**
     * Profile Resource
     */
    $router->get('/profile', 'ProfileController@index');
    $router->put('/profile', 'ProfileController@update');
    $router->put('/profile/settings', 'ProfileController@settings');
    $router->put('/profile/update-avatar', 'ProfileController@updateAvatar');
    $router->put('/profile/change-password', 'ProfileController@changePassword');


    /**
     * Wish-list: Danh sách ưu thích
     */
    resource('/wish-list', 'WishListController', $router);

    /**
     *  Resource
     */
    $router->get('/ticket/status', 'TicketController@ticketStatus');
    resource('/ticket', 'TicketController', $router);

    /**
     *  comment-ticket
     */
    resource('/comment-tickets', 'CommentTicketController', $router);

    //resource('/reviews', 'RoomReviewController', $router);
    $router->get('/reviews/show-reviews/{id}', 'RoomReviewController@show');
    $router->get('/get-room-for-review/{id}', 'RoomReviewController@getRoomForReview');
});


/**
 * Room Review Resource
 */
$router->get('/reviews/reviews-like-list', 'RoomReviewController@reviewLikeList');
$router->get('/reviews/reviews-service-list', 'RoomReviewController@reviewServiceList');
$router->get('/reviews/reviews-quality-list', 'RoomReviewController@reviewQualityList');
$router->get('/reviews/reviews-cleanliness-list', 'RoomReviewController@reviewCleanlinessList');
$router->get('/reviews/reviews-valuable-list', 'RoomReviewController@reviewValuableList');
$router->get('/reviews/reviews-recommend-list', 'RoomReviewController@reviewRecommendList');

$router->get('/reviews/{id}', 'RoomReviewController@linkReview');
$router->post('/reviews', 'RoomReviewController@store');

/*
 * Rooms Router
 */
$router->get('/rooms/type', 'RoomController@getRoomType');
$router->get('/rooms/rent-type', 'RoomController@roomRentType');
$router->get('/rooms/room-lat-long', 'RoomController@getRoomLatLong');

$router->get('/rooms/schedule/{id}', 'RoomController@getRoomSchedule');
$router->get('/rooms/schedule-by-hour/{id}', 'RoomController@getRoomScheduleByHour');
$router->get('/rooms/room_recommend/{id}', 'RoomController@getRoomRecommend');
$router->get('/rooms/count-room-by-standard-point', 'RoomController@getCountRoomByStandardPoint');
$router->get('/rooms/count-room-by-comfort-lists', 'RoomController@getCountRoomByComfortLists');
$router->get('/rooms/number-room-by-city', 'RoomController@countNumberOfRoomByCity');
$router->get('/rooms/{id}', 'RoomController@show');
$router->get('/rooms', 'RoomController@index');


/**
 * Comfort Resource
 */
resource('/comforts', 'ComfortController', $router);

/**
 * City Resource
 */
resource('/cities', 'CityController', $router);


/**
 * District Resource
 */
resource('/districts', 'DistrictController', $router);

/**
 * SEARCH
 */
$router->get('/search-suggestions', 'SearchController@searchSuggestions');

/*
 * Booking Router
 */
$router->post('/bookings', 'BookingController@store');
$router->post('/bookings/price-calculator', 'BookingController@priceCalculator');

// thanh toan
$router->get('/bank-list/{uuid}', 'BookingController@bankList');
$router->post('/payment/{uuid}', 'BookingController@payment');
/**
 * Router login, register , reset pass, forget pass
 */
$router->post('login', 'LoginController@login');
$router->post('register', 'RegisterController@register');
$router->put('register/email-confirm/{uuid}', 'RegisterController@confirm');
$router->post('reset-password/{time}', 'ResetPasswordController@resetPassword');
$router->get('set-password/{time}', 'ResetPasswordController@getFormResetPassword');
$router->post('forget-password', 'ForgetPasswordController@forgetPassword');

//// Social login
//$router->get('login/{social}', 'SocialAuthController@social');

/**
 * Router Coupon
 */
$router->post('coupons/calculate-discount', 'CouponController@calculateDiscount');
$router->get('coupons/status-list', 'CouponController@statusList');
$router->get('coupons/all-day-list', 'CouponController@allDayList');
resource('/coupons', 'CouponController', $router);

/**
 * Router Promotion
 */
$router->get('promotions/status-list', 'PromotionController@statusList');
resource('/promotions', 'PromotionController', $router);

/* thanh toan*/

$router->get('/success', 'PaymentHistoryController@success');
$router->get('/cancel/{code}', 'PaymentHistoryController@cancel');

/* Settings*/

resource('/settings', 'SettingController', $router);

/**
 * Blogs Resource
 */
$router->get('/blogs/status-list', 'BlogController@statusList');
$router->get('/blogs/hot-list', 'BlogController@hotList');
$router->get('/blogs/new-list', 'BlogController@newList');
resource('/blogs', 'BlogController', $router);

/**
 * Category Resource
 */
$router->get('/categories/status-list', 'CategoryController@statusList');
$router->get('/categories/hot-list', 'CategoryController@hotList');
$router->get('/categories/new-list', 'CategoryController@hotList');
resource('/categories', 'CategoryController', $router);


/**
 * Place Resource
 */
resource('/places', 'PlaceController', $router);