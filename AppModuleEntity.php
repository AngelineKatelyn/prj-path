<?php
/**
 *File name : AppModuleEntity.php  / Date: 11/26/2021 - 10:55 AM
 *Code Owner: Dao Thi Minh Nguyet / Phone: 0985455294 / Email: nguyetdtm@omt.vn
 */

namespace App\Entities;


use App\Models\AppModule\AppModule;
use App\Models\AppModule\AppModuleSchool;
use Illuminate\Support\Facades\DB;

class AppModuleEntity
{
    public function querySchoolAppModules($schoolId, $relation = null)
    {
        $relation = is_null($relation) ? ['module'] : $relation;

        return AppModuleSchool::where('school_id', $schoolId)
            ->active()
            ->select('id', 'school_id', 'app_module_id')
            ->with($relation)
            ->get()
            ->toArray();
    }

    public function queryAllAppModules($relation = [])
    {
        return AppModule::active()
            ->select('id', 'name', 'code', 'key', 'app_type')
            ->with($relation)
            ->get()
            ->toArray();
    }

    public function queryAppModuleByKeyAndType($key, $appType)
    {
        return DB::table('app_modules')
            ->where('key', $key)
            ->where('app_type', $appType)
            ->where('is_deleted', UN_DELETED)
            ->select('id')
            ->get()
            ->toArray();
    }

    public function queryAppModulesByIds($moduleIds)
    {
        return DB::table('app_module_school')
            ->select('school_id')
            ->whereIn('app_module_id', $moduleIds)
            ->where('status', 1)
            ->get()
            ->toArray();
    }
}