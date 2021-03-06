<?php

namespace App\Http\Transformers\Customer;

use App\Http\Transformers\Traits\FilterTrait;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;
use App\Repositories\Comforts\Comfort;

class ComfortTransformer extends TransformerAbstract
{
    use FilterTrait;
    protected $availableIncludes = [
        'details'
    ];

    public function transform(Comfort $comfort = null)
    {
        if (is_null($comfort)) {
            return [];
        }

        return [
            'id'         => $comfort->id,
            'icon'       => $comfort->icon,
            'icon_name'  => $comfort->icon_name
        ];
    }

    public function includeDetails(Comfort $comfort = null, ParamBag $params = null)
    {
        if (is_null($comfort)) {
            return $this->null();
        }

        $locale = getLocale();

        $data = $this->pagination($params, $comfort->comfortTrans($locale));

        return $this->collection($data, new ComfortTranslateTransformer);
    }
}
