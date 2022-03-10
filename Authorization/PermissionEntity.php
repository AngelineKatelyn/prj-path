<?php


namespace App\Entities\Authorization;


use App\Helpers\Transformer\Access\PermissionTransformer;
use App\Models\Authorization\AccessPermission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class PermissionEntity
{

    protected $cacheKeys;
    protected $groupRepository;
    protected $transformer;

    public function __construct()
    {
        $this->cacheKeys   = Config::get('cache_keys.authorization.permissions');
        $this->transformer = new PermissionTransformer();
    }

    public function getAll($type = null)
    {
        $permissions = Cache::get($this->cacheKeys['list']);
        $permissions = !is_null($permissions) && count($permissions) > 0 ? $permissions : $this->updateListPermissionCache();

        return !is_null($type) && count($permissions) > 0 ? $permissions->where('type', $type) : $permissions;
    }


    public function transformPermissionInformation($permission)
    {
        return $this->transformer->transform($permission);
    }

    public function updateListPermissionCache()
    {
        $permissions = $this->transformer->transformCollection(AccessPermission::all());
        Cache::put($this->cacheKeys['list'], $permissions, 1440);

        return $permissions;
    }


}