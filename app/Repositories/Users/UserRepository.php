<?php

namespace App\Repositories\Users;

use App\Repositories\BaseRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    use FilterTrait, PresentationTrait;
    /**
     * User model.
     * @var Model
     */
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param array $data
     *
     * @return \App\Repositories\Eloquent
     */
    public function store($data)
    {
        $user  = parent::store($data);
        $roles = array_get($data, 'roles', []);
        if (count($roles)) {
            $user->roles()->attach($roles);
        }
        return $user;
    }

    public function update($id, $data, $except = [], $only = [])
    {
        $user = parent::update($id, $data);
        $roles = array_get($data, 'roles', []);
        $user->roles()->detach();
        $user->roles()->attach($roles);
        return $user;
    }


    /**
     * Cập nhập thông tin cho customer
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param int $id
     * @param $data
     * @param array $except
     * @param array $only
     * @return \App\Repositories\Eloquent
     */
    public function updateInfoCustomer($id, $data, $except = [], $only = [])
    {
        $data = array_only($data, $only);

        if (isset($data['subcribe']) && empty($data['subcribe']))
        {
            $data['subcribe'] =1;
        }

       if (isset( $data['settings']))
       {
           $data['settings'] = json_encode( $data['settings']);
       }

        $user = parent::update($id, $data);
        return $user;
    }

    /**
     *  Cập nhập settings cho customer
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     * @param $data
     * @param array $except
     * @param array $only
     * @param array $list
     * @return \App\Repositories\Eloquent
     */
    public function updateSettingCustomer($id, $data, $except = [], $only = [],$list= [])
    {
        $data = array_only($data, $only);

        if (empty($data['subcribe']))
        {
            $data['subcribe'] =1;
        }
        if (isset($data['settings']) && !empty($data['settings']))
        {
            foreach ($data['settings'] as $k => $val)
            {
                if (empty($val))
                {
                    $list[$k] = User::DISABLE;
                }else
                {
                    $list[$k] = $data['settings'][$k];
                }
            }
        }

        $data['settings'] = json_encode($list);
        $user = parent::update($id, $data);
        return $user;

    }


    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $user
     * @param $request
     * @throws \Exception
     */
    public function checkValidPassword($user ,$request)
    {

      if (!Hash::check($request['old_password'],$user->password))
      {
          throw new \Exception('Mật khẩu không chính xác');

      }elseif($request['old_password'] === $request['password'])
      {
          throw new \Exception('Mật khẩu không được trùng với mật khẩu cũ');
      }


    }

    /**
     * Lấy dữ liệu về giới tính
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return mixed
     */
    public function getSexConstant()
    {
        return $this->model::SEX;
    }

    /**
     * Lấy thông tin về cấp độ
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return mixed
     */
    public function getLevelConstant()
    {
        return $this->model::LEVEL;
    }

    /**
     * Lấy thông tin về loại tài khoản
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return mixed
     */
    public function getAccountTypeConstant()
    {
        return $this->model::TYPE_ACCOUNT;
    }

    /**
     * Lấy thông tin user qua email hoặc SĐT
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param array $data
     *
     * @return array
     */
    public function getUserByEmailOrPhone($data = [])
    {
        $email = array_key_exists('email', $data) ? $data['email'] : 'Không xác định';
        $phone = array_key_exists('phone', $data) ? $data['phone'] : 'Không xác định';
        $data  = $this->model->where('email', $email)->orWhere('phone', $phone)->first();
        return $data;
    }


    /**
     * Lấy thông tin user thông qua uuid;
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $uuid
     * @return mixed
     */

    public function getUserByUuid($uuid){
        return $this->model->where('uuid', $uuid)->first();
    }


    /**
     * Lấy thông tin User theo uuid và status
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data
     * @return mixed
     */
    public function checkUserByStatus($data)
    {
        return $this->model->where('uuid', $data['uuid'])->where('status',1)->first();

    }


    /**
     * Update mã kích hoạt tài hoản cho người dùng
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data
     * @return \App\Repositories\Eloquent
     */

    public function updateStatus($data)
    {
        $uuid = $data['uuid'];
        $user = $this->getUserByUuid($uuid);
        $user = parent::update($user->id, $data);
        return $user;
    }


    /**
     * reset lại mật khẩu theo
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data
     * @return \App\Repositories\Eloquent|mixed
     */
    public function resetPasswordCustomer($data)
    {
        $uuid = $data['uuid'];
        $user = $this->getUserByUuid($uuid);

        $user = parent::update($user->id, $data);
        return $user;
    }

    /**
     *
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data
     * @throws \Exception
     */

     public function checkValidToken($data)
     {
         $token = isset($data['token']) ? $data['token'] : null;
         $uuid = $data['uuid'];
         $user = $this->getUserByUuid($uuid);
         if ($user->token  != $token) throw new \Exception('Đường dẫn không tồn tại');

     }


    /**
     * Kiểm tra thời gian tồn tại của đường link xác nhận mật khẩu
   * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $code
     *
     * @return int
     */
    public function checkTime($code)
    {
        $timeNow    = Carbon::now();
        $timeSubmit = base64_decode($code);
        $timeSubmit = Carbon::createFromTimestamp($timeSubmit)->toDateTimeString();
        $minutes    =  $timeNow->diffInMinutes($timeSubmit);
        // Nếu sao 60 phút khách hàng không phản hồi thì đườndẫn bị hủy
        if ($minutes > 60) {
            throw new \Exception('Đường dẫn không tồn tại ');
        }

    }

    /**
     *  Trong thời gian đườngg link reset password còn tồn tại chỉ được update một l
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data
     */

    public function checkUpdate($data)
    {
        $uuid = $data['uuid'];
        $user = $this->getUserByUuid($uuid);

        $timeNow    = Carbon::now();
        $minutes    =  $timeNow->diffInMinutes($user->updated_at);
        // Nếu sao 60 phút khách hàng không phản hồi thì đườndẫn bị hủy
        if ($minutes < 60) {
            throw new \Exception('60 phút sau bạn mới có thể update');
        }


    }


}
