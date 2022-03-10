<?php
/**
 *File name : UserEntity.php  / Date: 11/6/2021 - 9:50 AM
 *Code Owner: Dao Thi Minh Nguyet / Phone: 0985455294 / Email: nguyetdtm@omt.vn
 */

namespace App\Entities;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UserEntity
{

    protected $usernameCacheKey;
    protected $userListClassCacheKey;

    public function __construct()
    {
        $this->usernameCacheKey      = Config::get('cache_keys.users.username');
        $this->userListClassCacheKey = Config::get('cache_keys.users.list_class');
    }

    public function getUserInfoById($userId)
    {
        $user = (new User())->getModelCache($userId);

        if (is_null($user)) {
            return $user;
        }

        return $user;
    }

    public function getUserInfoByUsername($userName, $relation = null)
    {
        $user = Cache::get($this->usernameCacheKey.$userName);

        if (!is_null($user)) {
            return $user;
        }
        $relation = is_null($relation) ? ['roles'] : $relation;
        $user     = $this->queryUserByUsername($userName, $relation);

        Cache::put($this->usernameCacheKey.$userName, $user, Carbon::now()->addMinutes(10));

        return $user;
    }

    public function queryUserByUsername($userName, $relation = [])
    {
        return User::where('username', $userName)
            ->where('status', '!=', STATUS_INACTIVE)
            ->with($relation)
            ->first();
    }

    public function queryUserBySisId($sisId, $relation = [])
    {
        return User::where('sis_id', $sisId)
            ->where('status', '!=', STATUS_INACTIVE)
            ->with($relation)
            ->first();
    }

    public function getSchoolIdsOfUser($userId)
    {
        $schoolUserIds    = $this->querySchoolIdsOfUserStaff($userId);
        $schoolManagerIds = $this->querySchoolIdsOfUserManager($userId);

        return array_unique(array_merge(array_column($schoolUserIds, 'school_id'),
            array_column($schoolManagerIds, 'school_id')));
    }

    public function querySchoolIdsOfUserStaff($userId, $relation = [])
    {
        return SchoolUser::where('user_id', $userId)
            ->select('school_id')
            ->with($relation)
            ->get()
            ->toArray();
    }

    public function querySchoolIdsOfUserManager($userId, $relation = [])
    {
        return DB::connection(KO_TEACHERS)->table('school_managers')
            ->where('user_id', $userId)
            ->select('school_id')
            ->get()
            ->toArray();
    }

    public function queryClassIdOfUserTeacher($userId, $relation = [])
    {
        return ClassUser::where('teacher_id', $userId)
            ->active()
            ->with($relation)
            ->get()
            ->toArray();
    }

    public function checkHasRoles($user, $roles)
    {
        $userRoleCodes = collect($user->roles)->pluck('code')->toArray();

        foreach ($roles as $roleCode) {
            if (collect($userRoleCodes)->contains($roleCode)) {
                return true;
            }
        }

        return false;
    }

    public function getFullName($user)
    {
        $name = $user['middle_name'] == '' ? $user['last_name'].' '.$user['first_name'] :
            $user['last_name'].' '.$user['middle_name'].' '.$user['first_name'];

        return convertUnicode($name);
    }

}
