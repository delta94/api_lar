<?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 27/09/2018
 * Time: 13:28
 */

namespace App\Repositories\Blogs;

use App\Repositories\BaseRepository;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    protected $model;
    protected $blog;

    /**
     * TagRepository constructor.
     *
     * @param Tag $tag
     */
    public function __construct(Tag $tag)
    {
        $this->model        = $tag;
    }

    /**
     * Kiểm tra xem những thẻ tag nào chưa tồn tại thì thêm mới, những tag tồn tại rồi
     * thì lấy ra id để thêm vào blog_tag
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $blog
     * @param array $data
     * @param array $tag
     * @param array $list
     * @return array
     */
    public function storeTag($data = [])
    {
        if (!empty($data['tags']['data'])) {
            $arr = explode(',', $data['tags']['data'][0]['name']);
            $test_tag = $this->getTagName($arr);
            $tag_name = array_map(function ($item) {
                return $item['name'];
            }, $test_tag);
            $result = array_diff($arr, $tag_name);
            $insert_tag=  array_map(function ($value) {
                $list = [
                        'name' => $value,
                        'slug'=> str_slug($value, '-')
                    ];
                return $list;
            }, $result);
            parent::storeArray($insert_tag);
            $list_tag= $this->getTagName($arr);
            $list_id = array_map(function ($value) {
                return $value['id'];
            }, $list_tag);
            return $list_id;
        }
    }




    public function getTagName($arr)
    {
        return $this->model->whereIn('tags.name', $arr)->get()->toArray();
    }
}
