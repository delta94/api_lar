<?php

namespace App\Http\Controllers\Api;

use App\Http\Transformers\ComfortTransformer;
use App\Repositories\Comforts\ComfortRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComfortController extends ApiController
{
    protected $validationRules
        = [
            'details.*.name' => 'required|unique:comfort_translates,name',
        ];
    protected $validationMessages
        = [
            'details.*.name.required' => 'Tên không được để trông',
            'details.*.name.unique'   => 'Tiện ích này đã tồn tại',

        ];

    /**
     * ComfortController constructor.
     *
     * @param ComfortRepository $comfort
     */
    public function __construct(ComfortRepository $comfort)
    {
        $this->model = $comfort;
        $this->setTransformer(new ComfortTransformer);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('comfort.view');
            $pageSize    = $request->get('limit', 25);
            $this->trash = $this->trashStatus($request);
            $data        = $this->model->getByQuery($request->all(), $pageSize, $this->trash);
            return $this->successResponse($data);
        } catch (AuthorizationException $f) {
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
            $this->authorize('comfort.view');
            $trashed = $request->has('trashed') ? true : false;
            $data    = $this->model->getById($id, $trashed);
            return $this->successResponse($data);
        } catch (AuthorizationException $f) {
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
        DB::enableQueryLog();
        try {
            $this->authorize('comfort.create');
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $this->model->store($request->all());
//           dd(DB::getQueryLog());
            DB::commit();

            logs('comfort', 'tạo comfort mã ' . $data->id, $data);

            return $this->successResponse($data, true, 'details');
        } catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollBack();
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
        DB::beginTransaction();
        DB::enableQueryLog();
        try {
            $this->authorize('comfort.update');
            $this->validate($request, [], []);
            $data = $this->model->update($id, $request->all());
            DB::commit();
            logs('comfort', 'sửa comfort mã ' . $data->id, $data);

            //dd(DB::getQueryLog());
            return $this->successResponse($data, true, 'details');
        } catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
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
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        DB::enableQueryLog();
        try {
            $this->authorize('comfort.delete');
            $this->model->deleteComfort($id);
//            dd(DB::getQueryLog());
            DB::commit();
            logs('comfort', 'xóa tiện ích mã ' . $id);
            //dd(DB::getQueryLog());
            return $this->deleteResponse();
        } catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //DB::rollBack();
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            //DB::rollBack();
            throw $e;
        } catch (\Throwable $t) {
            //DB::rollBack();
            throw $t;
        }
    }
}
