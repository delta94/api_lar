<?php

namespace App\Http\Controllers\ApiCustomer;

use App\Events\Customer_Register_Event;
use App\Http\Transformers\UserTransformer;
use App\Repositories\Users\UserRepository;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Events\Customer_Register_TypeBooking_Event;
use App\Repositories\Referrals\ReferralRepositoryInterface;
use App\Repositories\Coupons\CouponLogic;
use App\Jobs\SendCouponRegisterUser;

class RegisterController extends ApiController
{
    protected $validationRules = [
        'email'                 => 'required|email|max:255|unique:users,email',
        'password'              => 'required|min:6|max:255',
        'password_confirmation' => 'required|min:6|max:255|same:password',
    ];

    protected $validationMessages = [
        'email.required'                    => 'Vui lòng nhập email',
        'email.email'                       => 'Email không đúng định dạng',
        'email.max'                         => 'Email cần nhỏ hơn :max kí tự',
        'email.unique'                      => 'Email đã được sử dụng',
        'password.required'                 => 'Vui lòng nhập mật khẩu',
        'password.min'                      => 'Mật khẩu cần lớn hơn :min kí tự',
        'password.max'                      => 'Mật khẩu cần nhỏ hơn :max kí tự',
        'password_confirmation.required'    => 'Vui lòng nhập mật khẩu',
        'password_confirmation.min'         => 'Mật khẩu cần lớn hơn :min kí tự',
        'password_confirmation.max'         => 'Mật khẩu cần nhỏ hơn :max kí tự',
        'password_confirmation.same'        => 'Mật khẩu không khớp nhau',
    ];

    public function __construct(UserRepository $user, UserTransformer $transformer, ReferralRepositoryInterface $referral, CouponLogic $coupon)
    {
        $this->user     = $user;
        $this->referral = $referral;
        $this->coupon   = $coupon;
        $this->setTransformer($transformer);
    }

    public function register(Request $request)
    {
        DB::enableQueryLog();
        try {
            $this->validationRules['email']                 = 'required|email|max:255';
            $this->validationRules['password']              = '';
            $this->validationRules['password_confirmation'] = '';
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $user = $this->user->checkEmailOrPhone($request->all());
//             dd(DB::getQueryLog());
            // Nếu đã tồn tại email này trên hệ thống với kiểu tao theo tự động theo booking
            // mà ở trạng thái chưa kích hoạt
            // thì gửi cho nó cái mail để thiết lập mật khẩu.
            if (!empty($user)) {
                $timeNow = Carbon::now();
                $minutes    =  $timeNow->diffInMinutes($user['updated_at']);
                if ($minutes < 1440) {
                    return $this->successResponse(['data' => ['message' => 'Bạn hãy vui lòng check mail để thiết lập mật khẩu']], false);
                }

                event(new Customer_Register_TypeBooking_Event($user));
                $data['updated_at'] = Carbon::now();
                $this->user->update($user->id, $data);


                return $this->successResponse(['data' => ['message' => 'Bạn hãy vui lòng check mail để thiết lập mật khẩu']], false);
            }

            $this->validationRules['email']                 = 'required|email|max:255|unique:users,email';
            $this->validationRules['password']              = 'required|min:6|max:255';
            $this->validationRules['password_confirmation'] = 'required|min:6|max:255|same:password';
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params           = $request->only('email', 'password');

            if ($request->get('ref') !== null) {
                if (!$this->user->isValidReferenceID($request->get('ref'))) {
                    $params['ref_code'] = null;
                } else {
                    $params['ref_code'] = $request->get('ref');
                    $user_id = $this->user->getIDbyUUID($request->get('ref'));
                }
            }

            // dd($user_id);
            $username           = $params['email'];
            $password           = $params['password'];
            // Create new user
            // dd($params);
            $newClient = $this->getResource()->store($params);

            if ($newClient && isset($params['ref_code'])) {
                $storeReferral  = $this->referral->storeReferralUser($newClient->id, $user_id);
                $coupon         = $this->coupon->createReferralCoupon($newClient);
                // dd($coupon);
                if ($coupon) {
                    $user_ref = $this->user->getById($newClient->id);
                    $coupon   = $this->coupon->getById($coupon->id);
                    $sendCouponRegister = (new SendCouponRegisterUser($newClient, $coupon))->delay(Carbon::now()->addHours(24));
                    dispatch($sendCouponRegister);
                }
            }

            // Issue token
            $guzzle  = new Guzzle;
            $url     = env('APP_URL') . '/oauth/token';
            $options = [
                'json'   => [
                    'grant_type'    => 'password',
                    'client_id'     => env('CLIENT_ID', 0),
                    'client_secret' => env('CLIENT_SECRET', ''),
                    'username'      => $username,
                    'password'      => $password,
                ],
                'verify' => false,
            ];
            $result  = $guzzle->request('POST', $url, $options)->getBody()->getContents();
            $result  = json_decode($result, true);

            event(new Customer_Register_Event($newClient));
            return $this->successResponse($result, false);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollBack();
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            DB::rollBack();
            return $this->errorResponse([
                'errors' => ['Tài khoản customer chưa được xác thực.'],
            ], $clientException->getCode());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function getResource()
    {
        return $this->user;
    }

    /**
     * Update status cuả nguời dùng khi xác nhận email
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */

    public function confirm(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user->checkUserByStatus($request->all());
            if (!empty($user)) {
                throw new \Exception('Tài khoản đã được kích hoạt');
            }
            $validate = array_only($this->validationRules, [
                'status',
            ]);
            $this->validate($request, $validate, $this->validationMessages);
            $data = $this->user->updateStatus($request->all());
            DB::commit();
            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollBack();
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }
}
