<?php
/**
 *File name : AccessPermissionGroupEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\Authorization;


use App\Helpers\Transformer\Access\AccessPermissionGroupTransformer;
use App\Helpers\Transformer\Access\PermissionTransformer;
use App\Models\Authorization\AccessPermissionGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AccessPermissionGroupEntity
{

    protected $cacheKeys;
    protected $transformer;
    protected $moetUnit;
    protected $moetUnitEntity;
    protected $permissionTranformer;

    public function __construct()
    {
        $this->cacheKeys            = Config::get('cache_keys.authorization.permission_groups');
        $this->transformer          = new AccessPermissionGroupTransformer();
        $this->permissionTranformer = new PermissionTransformer();
    }

    public function getAllGroups()
    {
        $groups = Cache::get($this->cacheKeys['all']);
        if (is_null($groups)) {
            return $this->updateCacheAllGroups();
        }
        return $groups;
    }

    public function updateCacheAllGroups()
    {
        $groups = $this->transformer->transformCollection(AccessPermissionGroup::with(['permissions'])
            ->get()
            ->transform(function($group) {
                $group->permissions = $this->permissionTranformer->transformCollection($group->permissions);
                return $group;
            })
            ->toArray());
        Cache::put($this->cacheKeys, $groups, Carbon::now()->addHours(12));
        return $groups;
    }

}
