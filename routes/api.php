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
$router->group([
    'middleware' => 'auth',
], function ($router) {
    /**
     * Get config
     */
    $router->get('/permissions', 'PermissionController@index');
    $router->get('/languages', 'LanguageController@index');

    /**
     * Log Resource
     */
    resource('/logs', 'LogController', $router);


    /**
     * User Resource
     */
    $router->get('/users/sex-list', 'UserController@sexList');
    $router->get('/users/level-list', 'UserController@levelList');
    $router->get('/users/account-type-list', 'UserController@accountTypeList');
    resource('/users', 'UserController', $router);
    /**
     * Profile Resource
     */
    $router->get('/profile', 'ProfileController@index');
    $router->put('/profile', 'ProfileController@update');
    $router->put('/profile/change-password', 'ProfileController@changePassword');


    $router->get('/permissions', 'PermissionController@index');
    /**
     * Role Resource
     */
    resource('/roles', 'RoleController', $router);

    /**
     * Room Resource
     */
    $router->get('/rooms/type', 'RoomController@getRoomType');
    $router->get('/rooms/media-type', 'RoomController@roomMediaType');
    $router->get('/rooms/rent-type', 'RoomController@roomRentType');
    $router->get('/rooms/room-status', 'RoomController@roomStatus');
    $router->put('/rooms/prop-update/{id}', 'RoomController@minorRoomUpdate');
    resource('/rooms', 'RoomController', $router);


    /**
     * City Resource
     */
    resource('/cities', 'CityController', $router);

    /**
     * District Resource
     */
    resource('/districts', 'DistrictController', $router);

    /**
     * Comfort Resource
     */
    resource('/comforts', 'ComfortController', $router);

    /**
     * Booking Resource
     */
    $router->get('/bookings/booking-status-list', 'BookingController@bookingStatusList');
    $router->get('/bookings/booking-type-list', 'BookingController@bookingTypeList');
    $router->get('/bookings/type-list', 'BookingController@typeList');
    $router->get('/bookings/payment-method-list', 'BookingController@paymentMethodList');
    $router->get('/bookings/payment-status-list', 'BookingController@paymentStatusList');
    $router->get('/bookings/payment-history-type-list', 'BookingController@paymentHistoryTypeList');
    $router->get('/bookings/booking-source-list', 'BookingController@bookingSourceList');
    $router->get('/bookings/price-range-list', 'BookingController@priceRangeList');
    $router->post('/bookings/price-calculator', 'BookingController@priceCalculator');
    $router->put('/bookings/status-update/{id}', 'BookingController@minorBookingUpdate');
    $router->put('/bookings/money-update/{id}', 'BookingController@updateBookingMoney');

    resource('/bookings', 'BookingController', $router);
    /**
     * Payment History
     */
    $router->get('/payments/payment-history-status', 'PaymentHistoryController@paymentHistoryStatus');
    resource('/payments', 'PaymentHistoryController', $router);

    /**
     * Category Resource
     */
    $router->get('/categories/status-list', 'CategoryController@statusList');
    $router->get('/categories/hot-list', 'CategoryController@hotList');
    $router->put('/categories/single-update/{id}', 'CategoryController@singleUpdate');
    resource('/categories', 'CategoryController', $router);
    /**
     * Blogs Resource
     */
    $router->get('/blogs/status-list', 'BlogController@statusList');
    $router->get('/blogs/hot-list', 'BlogController@hotList');
    $router->put('/blogs/single-update/{id}', 'BlogController@singleUpdate');
    resource('/blogs', 'BlogController', $router);
});


$router->post('login', 'LoginController@login');
$router->post('register', 'RegisterController@register');
// Social login
$router->get('login/{social}', 'SocialAuthController@social');


/**
 * resource router helper
 * @author SaturnLai <daolvcntt@gmail.com>
 * @date   2018-07-17
 *
 * @param  string                       $uri        enpoint url
 * @param  string                       $controller controller name
 * @param  Laravel\Lumen\Routing\Router $router     RouterObject
 */
function resource($uri, $controller, Laravel\Lumen\Routing\Router $router)
{
    $router->get($uri, $controller . '@index');
    $router->get($uri . '/{id}', $controller . '@show');
    $router->post($uri, $controller . '@store');
    $router->put($uri . '/{id}', $controller . '@update');
    $router->delete($uri . '/{id}', $controller . '@destroy');
}
