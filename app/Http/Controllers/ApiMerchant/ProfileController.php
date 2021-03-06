<?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 18/12/2018
 * Time: 10:08
 */

namespace App\Http\Controllers\ApiMerchant;

use App\Http\Controllers\ApiController;
use App\Http\Transformers\Merchant\UserTransformer;
use App\Repositories\Users\UserRepository;
use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Exception\ImageException;

class ProfileController extends ApiController
{
    protected $validationRules
        = [
            'name'                      => 'v_title',
            'email'                     => 'bail|required|email|unique:users,email',
            'old_password'              => 'required',
            'password'                  => 'required|min:6|confirmed',
            'phone'                     => 'bail|nullable|min:9',
            'birthday'                  => 'bail|nullable|date_format:"Y-m-d"',
            'gender'                    => 'bail|required|between:0,3',
            'city_id'                   => 'integer|exists:cities,id',
            'district_id'               => 'integer|exists:districts,id',
            'account_branch'            => 'required',

            /**
             * settings
             */
            'status'                    => 'bail|nullable|integer|in:1',
            'settings.*'                => 'bail|nullable|integer|in:0',



        ];
    protected $validationMessages
        = [
            'name.v_title'              =>  'Tên không đúng định dạng',
            'email.required'            => 'Email không được để trông',
            'email.email'               => 'Email không đúng định dạng',
            'email.unique'              => 'Email đã tồn tại trên hệ thống',
            'password.required'         => 'Mật khẩu không được để trống',
            'password.min'              => 'Mật khẩu phải có ít nhât  ký tự',
            'password.confirmed'        => 'Mật khẩu không trùng khớp',
            'old_password.required'     => 'Mật khẩu không được để trống',
            'phone.min'                 => 'Số điện thoại phải tối thiểu 9 chữ số',
            'gender.required'           => 'Giới tính không được để trống',
            'gender.between'            => 'Giới tính không hợp lệ',
            'subcribe.integer'          => 'Trường này phải là kiểu số',
            'subcribe.in'               => 'Trường này không hợp lệ',
            'subcribe.between'          => 'Trường này không hợp lệ',
            'subcribe.filled'           => 'Phải tồn tại ít nhất mã trạng thái',
            'settings.*.integer'        => 'Trường này phải là kiểu số',
            'settings.*.in'             => 'Trường này không hợp lệ',
            'settings.*.between'        => 'Trường này không hợp lệ',
            'city_id.exists'            => 'Thành phố không tồn tại',
            'district_id.exists'        => 'Quận / Huyện không tồn tại',
            'account_branch.required'   => 'Chi nhánh ngân hàng không được bỏ trống',
            'account_number.required'   => 'Số tài khoản ngân hàng không được bỏ trống',
        ];

    /**
     * UserController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepositoryInterface $user)
    {
        $this->model = $user;
        $this->setTransformer(new UserTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Responsese
     */
    public function index(Request $request)
    {
        return $this->successResponse($request->user());
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validationRules = array_only($this->validationRules, ['name', 'phone','email','gender','account_number','birthday','address','avatar','avatar_url','settings','subcribe','city_id','district_id','account_branch']);
            $this->validationRules['email'] .= ',' . $request->user()->id;
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $model = $this->model->updateInfoCustomer($request->user()->id, $request->all(), [], ['name', 'phone','email','gender','account_number','birthday','address','avatar','avatar_url','settings','subcribe','city_id','district_id','account_branch']);
            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (ImageException $imageException) {
            return $this->notSupportedMediaResponse([
                'error' => $imageException->getMessage(),
            ]);
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */

    public function changePassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $this->model->checkValidPassword($request->user(), $request->all());
            $this->model->updateInfoCustomer($request->user()->id, $request->all(), [], ['password']);
            DB::commit();
            return $this->successResponse(['data' => ['message' => 'Đổi mật khẩu thành công']], false);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Cập nhật ảnh avatar
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function updateAvatar(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->validationRules = array_only($this->validationRules, ['avatar']);
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $model = $this->model->updateAvatar($request->user()->id, $request->all(), [], ['avatar']);
            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (ImageException $imageException) {
            return $this->notSupportedMediaResponse([
                'error' => $imageException->getMessage(),
            ]);
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }
}
