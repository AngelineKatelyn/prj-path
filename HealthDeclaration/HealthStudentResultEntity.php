<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\StudentHealthResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class HealthStudentResultEntity
{

    protected $model;

    public function __construct()
    {
        $this->model = new StudentHealthResult();
    }

    public function getStudentResultWithAnswerIdsByDate($answerIds, Carbon $date)
    {
        return $this->model->where('date', $date->toDateString())
            ->whereIn('answer_id', $answerIds)
            ->with('student', 'student.school', 'student.thisClass', 'student.school_year', 'student.grade')
            ->get()->toArray();
    }

}
