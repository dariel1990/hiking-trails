<?php

namespace App\Models\Concerns;

use App\Support\AppClient;

/**
 * How a record is identified in map links (e.g. /map?business=…), the same
 * way trails are: browsers get the readable slug, while the apps (which parse
 * the parameter as an integer) and records without a slug get the ID.
 *
 * @property int $id
 * @property string|null $slug
 */
trait HasUrlKey
{
    public function urlKey(): int|string
    {
        return AppClient::isApp() || ! $this->slug ? $this->id : $this->slug;
    }
}
