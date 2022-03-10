<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\HealthStudentInfoBmi;

class HealthStudentInfoBmiEntity
{

    protected $model;

    public function __construct()
    {
        $this->model = new HealthStudentInfoBmi();
    }


    public function getBmiForStudent($student, $bmiInt)
    {
        $months         = calculateAgeMonths($student->user->date_of_birth);
        $studentBmiInfo = $this->model->where('gender', $student->user->gender)
            ->where('month', $months)
                ->with(['healthBmi', 'healthBmi.healthBmiType'])
            ->first();
        if (is_null($studentBmiInfo)){
            return null;
        }
        $healthsBmi     = collect($studentBmiInfo['health_bmi'])->sortBy('value');
        $healthBmi      = $healthsBmi->where('value', '<=', $bmiInt)->last();
        return !is_null($healthBmi) ? ($healthBmi['health_bmi_type'] ?? null) : null;
    }

}
