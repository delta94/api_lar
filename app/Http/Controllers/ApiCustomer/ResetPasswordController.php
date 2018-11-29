<?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 28/11/2018
 * Time: 14:23
 */

namespace App\Http\Controllers\ApiCustomer;


use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ResetPasswordController extends ApiController
{
    protected $user;
    protected $validationRules
        = [
            'password'                  => 'required|min:6|confirmed',
        ];

    protected $validationMessages
        = [
            'password.required'         => 'Mật khẩu không được để trống',
            'password.min'              => 'Mật khẩu phải có ít nhât  ký tự',
            'password.confirmed'        => 'Mật khẩu không trùng khớp',
        ];


    public function __construct(
        UserRepositoryInterface $user
    ) {
        $this->user           = $user;
    }
    public function resetPassword(Request $request, $time)
    {
        DB::beginTransaction();
        try {

            $this->user->checkValidToken($request->all());

            $this->user->checkTime($time);

            //  Trong thời gian đường dẫn còn tồn tại chỉ được update 1 l
            $this->user->checkUpdate($request->all());

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->user->resetPasswordCustomer($request->all(), [], ['password']);
            logs('user', 'Khôi phục mật khẩu ' . $data->email , $data);
            DB::commit();
           return $this->successResponse(['data' => ['message' => 'Đổi mật khẩu thành công']], false);

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
