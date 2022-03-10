<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\Authorization;


use App\Entities\MoetUnitEntity;
use App\Entities\UserEntity;
use App\Helpers\Transformer\Access\AccessRoleTransformer;
use App\Helpers\Transformer\UserTransformer;
use App\Models\Authorization\AccessRole;
use App\Models\Moet\MoetUnit;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AccessRoleEntity extends AuthorizationEntity
{

    protected $cacheKeys;
    protected $transformer;
    protected $moetUnit;
    protected $moetUnitEntity;
    protected $cacheKeysRolePermission;
    protected $userTransformer;

    public function __construct(MoetUnit $moetUnit)
    {
        $this->cacheKeys               = Config::get('cache_keys.authorization.roles');
        $this->cacheKeysRolePermission = $this->cacheKeys['permissions'].".";
        $this->moetUnit                = $moetUnit;
        $this->moetUnitEntity          = new MoetUnitEntity();
        $this->transformer             = new AccessRoleTransformer();
        $this->userTransformer         = new UserTransformer();
    }


    public function getAccessRolesOfUser($userId, $schoolId = null)
    {
        $roles = Cache::get($this->cacheKeys['roles_of_user'].$userId);
        $roles = is_null($roles) ? $this->updateUserAccessRoleCache($userId) : $roles;

        return !is_null($schoolId) && count($roles) > 0 ?
            array_filter($roles, function($role) use ($schoolId) {
                return $role['item_id'] == $schoolId && $role['item_type'] == 'App\School';
            })
            : $roles;
    }

    public function getRolesOfMoetUnit()
    {
        $moetUnitIdsOfUser = $this->moetUnitEntity->getListMoetUnitForUser(Auth::user());
        $roles             = $this->getListRolesByMoetUnitIds($moetUnitIdsOfUser);
        return $this->transformer->transformCollection($roles);
    }

    public function getListRolesByMoetUnitIds($moetUnitIds)
    {
        return AccessRole::whereIn('moet_unit_id', $moetUnitIds)->with('users')->get();
    }


    public function getPermissionsOfRole(AccessRole $role)
    {
        $permissions = Cache::get($this->cacheKeys['permissions'].$role->id);

        return !is_null($permissions) ? $permissions : $this->updatePermissionsOfRoleCache($role);
    }

    public function updatePermissionsOfRoleCache($role)
    {
        unset($role->permissions);
        $permissions = $role->permissions;
        Cache::forget($this->cacheKeysRolePermission);

        return $permissions;
    }

    public function getDataCreate()
    {
        $permissionGroupEntity = new AccessPermissionGroupEntity();
        return ['groups' => $permissionGroupEntity->getAllGroups()];
    }

    public function getUserOfRole($role_id)
    {
        $role = $this->getAccessRoleInfo($role_id, ['users']);
        return $role['users'];
    }

    public function getUsersUnassignedRole($role_id)
    {
        $role          = $this->getAccessRoleInfo($role_id, ['users']);
        $userRepo      = new UserRepository();
        $role['users'] = $userRepo->getUsersFromMoetUnit($this->moetUnit, [$role['code']]);
        return $role;
    }

    public function getAccessRoleInfo($role_id, $relation = [])
    {
        $role        = AccessRole::where('id', $role_id)->with($relation)->first();
        $role->users = $this->userTransformer->transformCollection($role->users);
        return $this->transformer->transform($role);
    }

}
