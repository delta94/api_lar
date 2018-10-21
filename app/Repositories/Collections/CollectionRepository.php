<?php

namespace App\Repositories\Collections;

use App\Repositories\BaseRepository;

class CollectionRepository extends BaseRepository
{
    /**
     * Collection model.
     * @var Model
     */
    protected $model;
    protected $collectionTranslate;

    /**
     * CollectionRepository constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection, CollectionTranslateRepository $collectionTranslate)
    {
        $this->model                = $collection;
        $this->collectionTranslate  = $collectionTranslate;
    }

    /**
     * Thêm mới dữ liệu vào collection, collection_translate và collection_r
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param array $data
     * @return \App\Repositories\Eloquent
     */
    public function store($data)
    {
        $data['image'] = rand_name($data['image']);
        $data_collection= parent::store($data);
        $this->collectionTranslate->storeCollectionTranslate($data_collection,$data);
        $this->storeCollectionRoom($data_collection, $data);
        return $data_collection;
    }

    /**
     * Thêm mới dữ liệu vào collection_room
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $data_collection
     * @param $data
     */
    public function storeCollectionRoom($data_collection, $data)
    {
        if (!empty ($data)) {
            if (isset($data['rooms'])) {
                $data['rooms'] = array_unique($data['rooms']);
                $data_collection->rooms()->attach($data['rooms']);
            }
        }
    }

    /**
     * Cập nhật  dữ liệu vào collection_room
     * @author ducchien0612 <ducen0612@gmail.com>
     *
     * @param $data_collection
     * @param $data
     */
    public function updateCollectionRoom($data_collection, $data)
    {
        if (!empty ($data)) {
            if (isset($data['rooms'])) {
                $data['rooms'] = array_unique($data['rooms']);
                $data_collection->rooms()->detach();
                $data_collection->rooms()->attach($data['rooms']);
            }
        }
    }

    /**
     * Cập nhật dữ liệu cho collection, collection_translate và collection_room
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param int $id
     * @param $data
     * @param array $excepts
     * @param array $only
     * @return \App\Repositories\Eloquent
     */

    public function update($id, $data, $excepts = [], $only = [])
    {
        $data['image'] = rand_name($data['image']);
        $data_collection = parent::update($id, $data);
        $this->collectionTranslate->updateCollectionTranslate($data_collection,$data);
        $this->updateCollectionRoom($data_collection, $data);
        return $data_collection;
    }

    /**
     * Xóa bản ghi  collections và collection_translate
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     */
    public function destroyColection($id)
    {
        $this->collectionTranslate->deleteCollectionTranslateByCollectionID($id);
        parent::destroy($id);
    }

    /**
     * Cập nhật một số trường trạng thái
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param $id
     * @param $data
     *
     * @return \App\Repositories\Eloquent
     */
    public function singleUpdate($id, $data)
    {
        $data_collection = parent::update($id, $data);
        return $data_collection;
    }


}
