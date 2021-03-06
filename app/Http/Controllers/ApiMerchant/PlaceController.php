<?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 18/12/2018
 * Time: 10:12
 */

namespace App\Http\Controllers\ApiMerchant;

use App\Http\Controllers\ApiController;
use App\Http\Transformers\PlaceTransformer;
use App\Repositories\Places\Place;
use App\Repositories\_Merchant\PlaceLogic;
use App\Repositories\Places\PlaceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
class PlaceController extends ApiController
{
    protected $validationRules = [
        'latitude'              => 'required',
        'longitude'             => 'required',
        'status'                => 'integer|between:0,1',
        'guidebook_category_id' => 'required|integer|exists:guidebook_category,id,deleted_at,NULL',

        'name'                  => 'required|min:10|max:255|v_title',
        'room_id'               => 'required|integer|exists:rooms,id,deleted_at,NULL',
        // 'description'             => 'required',

        // 'details.*.*.name'        => 'required|min:10|max:255|v_title',
        // 'details.*.*.description' => 'required',
        // 'details.*.*.lang'        => 'required|v_title',
    ];
    protected $validationMessages = [
        'latitude.required'                       => 'Vĩ độ không được để trống',
        'longitude.required'                      => 'kinh độ không được để trống',
        'status.integer'                          => 'Trạng thái không phải là dạng số',
        'status.between'                          => 'Trạng thái không phù hợp',
        'guidebook_category_id.required'          => 'Danh mục hướng dẫn không được để trống',
        'guidebook_category_id.integer'           => 'Mã danh mục hướng dẫn phải là kiểu số',
        'guidebook_category_id.exists'            => 'Danh mục hướng dẫn không tồn tại',

        'room_id.required'                        => 'Phòng không được để trống',
        'room_id.integer'                         => 'Mã phòng phải là kiểu số',
        'room_id.exists'                          => 'Phòng không tồn tại',

        'name.required'                           => 'Tên dịch địa điểm không được để trông',
        'name.min'                                => 'Tối thiểu 10 ký tự',
        'name.max'                                => 'Tối đa 255 ký tự',
        'name.v_title'                            => 'Không được có ký tự đặc biệt',
        //'description.required'           => 'Mô tả không được để trống',

        'places.*.name.required'                  => 'Tên dịch địa điểm không được để trông',
        'places.*.name.min'                       => 'Tối thiểu 10 ký tự',
        'places.*.name.max'                       => 'Tối đa 255 ký tự',
        'places.*.name.v_title'                   => 'Không được có ký tự đặc biệt',

        'places.*.latitude.required'              => 'Vĩ độ không được để trống',
        'places.*.longitude.required'             => 'kinh độ không được để trống',

        'places.*.guidebook_category_id.required' => 'Danh mục hướng dẫn không được để trống',
        'places.*.guidebook_category_id.integer'  => 'Mã danh mục hướng dẫn phải là kiểu số',
        'places.*.guidebook_category_id.exists'   => 'Danh mục hướng dẫn không tồn tại',
    ];

    /**
     * PlaceController constructor.
     * @param PlaceRepository $place
     */
    public function __construct(PlaceLogic $place)
    {
        $this->model = $place;
        $this->setTransformer(new PlaceTransformer);
    }

    /**
     * Display a listing of the resource.
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('place.view');
            $pageSize    = $request->get('limit', 25);
            $data        = $this->model->getByQuery($request->all(), $pageSize, $this->trash);
            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws Throwable
     */
    public function show(Request $request, $id)
    {
        try {
            $this->authorize('place.view');
            $data    = $this->model->getById($id);
            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
            DB::rollBack();
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

    /**
     * Store a record into database
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param Request $request
     *
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->authorize('place.create');
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->model->store($request->all());
            DB::commit();
            logs('place', 'tạo địa điểm mã ' . $data->id, $data);
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
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    /**
     * Update a record
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws Throwable
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->authorize('place.update');
            $validate = array_only($this->validationRules, [
                'latitude',
                'longitude',
                'status',
                'guidebook_category_id',
                'name',
            ]);
            $this->validate($request, $validate, $this->validationMessages);
            $model = $this->model->update($id, $request->all());
            DB::commit();
            return $this->successResponse($model);
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
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            throw $e;
        } catch (\Throwable $t) {
            throw $t;
        }
    }

    /**
     * Destroy a record
     *
     * @param $id
     *
     * @return mixed
     * @throws Throwable
     */
    public function destroy($id)
    {
        try {
            $this->authorize('place.delete');
            $this->model->delete($id);

            return $this->deleteResponse();
        }catch (AuthorizationException $f) {
            DB::rollBack();
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

    /**
     * Lấy ra các Trạng thái bài viết (theo status)
     * @author sonduc <ndson1998@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function statusList()
    {
        try {
            $this->authorize('place.view');
            $data = $this->simpleArrayToObject(Place::PLACE_STATUS);
            return response()->json($data);
        }catch (AuthorizationException $f) {
            DB::rollBack();
            return $this->forbidden([
                'error' => $f->getMessage(),
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Thực hiện cập nhật status
     * @author sonduc <ndson1998@gmail.com>
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function singleUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->authorize('place.update');
            $avaiable_option = ['status'];
            $option          = $request->get('option');
            if (!in_array($option, $avaiable_option)) {
                throw new \Exception('Không có quyền sửa đổi mục này');
            }

            $validate = array_only($this->validationRules, [
                $option,
            ]);
            $this->validate($request, $validate, $this->validationMessages);

            $data = $this->model->singleUpdate($id, $request->only($option));
            logs('places', 'sửa trạng thái của địa điểm có mã ' . $data->id, $data);
            DB::commit();
            return $this->successResponse($data);
        }catch (AuthorizationException $f) {
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
            return $this->errorResponse([
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Throwable $t) {
            DB::rollBack();
            throw $t;
        }
    }

    public function editRoomPlace(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->authorize('place.update');
            $validate = array_only($this->validationRules, [
                'room_id',
                'places',
            ]);
            $validate['places.*.name']                  = 'required|min:10|max:255|v_title';
            $validate['places.*.latitude']              = 'required';
            $validate['places.*.longitude']             = 'required';
            $validate['places.*.guidebook_category_id'] = 'required|integer|exists:guidebook_category,id,deleted_at,NULL';
            $this->validate($request, $validate, $this->validationMessages);

            $data = $this->model->editRoomPlace($request->all());
            DB::commit();
            return $this->successResponse($data, false);
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
