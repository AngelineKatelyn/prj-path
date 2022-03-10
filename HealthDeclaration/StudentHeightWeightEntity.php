<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\HealthDeclarationAnswer;
use App\Models\HealthDeclaration\StudentHealthHeightWeight;
use Illuminate\Support\Facades\Config;

class StudentHeightWeightEntity
{

    protected $model;

    public function __construct()
    {
        $this->model = new StudentHealthHeightWeight();
    }


    public function getRecordStudentHeightWeight($studentId)
    {
        return $this->model->where('student_id', $studentId)->orderByDesc('created_at')->first();
    }

}
