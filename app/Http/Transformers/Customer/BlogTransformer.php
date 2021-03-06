<?php

namespace App\Http\Transformers\Customer;

use App\Http\Transformers\Traits\FilterTrait;
use App\Repositories\Blogs\Blog;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class BlogTransformer extends TransformerAbstract
{
    use FilterTrait;
    protected $availableIncludes = [
        'tags',
        'categories',
        'user',
    ];

    public function transform(Blog $blog = null)
    {
        if (is_null($blog)) {
            return [];
        }

        return [
            'id'          => $blog->id,
            'image'       => $blog->image,
            'status'      => $blog->status ?? 0,
            'hot'         => $blog->hot ?? 0,
            'new'         => $blog->new ?? 0,
            'user_id'     => $blog->user_id,
            'title'       => $blog->title,
            'slug'        => $blog->slug,
            'content'     => $blog->content,
            'description' => $blog->description,
            'type'        => $blog->type,
            'category_id' => $blog->category_id
        ];
    }

    // /**
    //  * Thông tin chi tiết bài viết
    //  * @author ducchien0612 <ducchien0612@gmail.com>
    //  *
    //  * @param Blog|null     $blog
    //  * @param ParamBag|null $params
    //  *
    //  * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
    //  */
    // public function includeDetails(Blog $blog = null, ParamBag $params = null)
    // {
    //     if (is_null($blog)) {
    //         return $this->null();
    //     }

    //     $data = $this->pagination($params, $blog->blogTrans());

    //     return $this->collection($data, new BlogTranslateTransformer);
    //     //return $this->primitive($data);
    // }

    /**
     *     Danh sách thẻ tags theo bài viết
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Blog|null     $blog
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeTags(Blog $blog = null, ParamBag $params = null)
    {
        if (is_null($blog)) {
            return $this->null();
        }
        $data = $this->pagination($params, $blog->tags());
        return $this->collection($data, new TagTransformer);
    }

    /**
     * Xác định bài viết này thuộc danh mục nào
     * @author ducchien0612 <ducchien0612@gmail.com>
     *
     * @param Blog|null     $blog
     * @param ParamBag|null $params
     *
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\NullResource
     */
    public function includeCategories(Blog $blog = null, ParamBag $params = null)
    {
        if (is_null($blog)) {
            return $this->null();
        }
        $data = $this->pagination($params, $blog->categories());
        return $this->collection($data, new CategoryTransformer);
    }

    /**
     *
     * @author ducchien0612 <ducchien0612gmail.com>
     *
     * @param Blog|null $blog
     *
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */

    public function includeUser(Blog $blog = null)
    {
        if (is_null($blog)) {
            return $this->null();
        }
        return $this->item($blog->user, new UserTransformer);
    }
}
