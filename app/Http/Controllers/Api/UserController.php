<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\UserTransformer;
use App\Repositories\Users\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    protected $validationRules
        = [
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ];
    protected $validationMessages
        = [
            'name.required'      => 'Tên không được để trông',
            'email.required'     => 'Email không được để trông',
            'email.email'        => 'Email không đúng định dạng',
            'email.unique'       => 'Email đã tồn tại trên hệ thống',
            'password.required'  => 'Mật khẩu không được để trống',
            'password.min'       => 'Mật khẩu phải có ít nhât :min ký tự',
            'password.confirmed' => 'Nhập lại mật khẩu không đúng',
        ];

    /**
     * UserController constructor.
     *
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->model = $user;
        $this->setTransformer(new UserTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        DB::enableQueryLog();
       try {
           $this->authorize('user.view');
           $pageSize = $request->get('limit', 25);

           $this->trash = $this->trashStatus($request);
           $data        = $this->model->getByQuery($request->all(), $pageSize, $this->trash);
//        dd(DB::getQueryLog());
           return $this->successResponse($data);
       }catch (AuthorizationException $f) {
           return $this->forbidden([
               'error' => $f->getMessage(),
           ]);
       }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $this->authorize('user.view');
            $trashed = $request->has('trashed') ? true : false;
            $data    = $this->model->getById($id, $trashed);
            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->authorize('user.create');
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $this->model->store($request->all());
            DB::commit();
            logs('user', 'tạo user mã '. $data->id);
            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function update(Request $request, $id)
    {
        $this->validationRules['email'] .= ',' . $id;

        unset($this->validationRules['password']);
        DB::beginTransaction();
        try {
            $this->authorize('user.update');
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $this->model->update($id, $request->all());
            DB::commit();
            logs('user', 'sửa user mã '. $data->id);

            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors'    => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->authorize('user.delete');
            $this->model->delete($id);
            DB::commit();
            logs('user', 'sửa user mã '. $id);
            return $this->deleteResponse();
        }catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    /**
     * Danh sách giới tính
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sexList()
    {
        try {
            $data = $this->simpleArrayToObject($this->model->getSexConstant());

            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Danh sách cấp độ
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function levelList()
    {
        try {
            $data = $this->simpleArrayToObject($this->model->getLevelConstant());

            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Danh sách loại tài khoản
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function accountTypeList()
    {
        try {
            $data = $this->simpleArrayToObject($this->model->getAccountTypeConstant());

            return response()->json($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
