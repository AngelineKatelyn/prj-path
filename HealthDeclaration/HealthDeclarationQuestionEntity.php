<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\HealthDeclarationAnswer;
use App\Models\HealthDeclaration\HealthDeclarationQuestion;
use App\Models\HealthDeclaration\HealthDeclarationType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class HealthDeclarationQuestionEntity
{

    protected $model;
    protected $answerEntity;

    public function __construct()
    {
        $this->answerEntity = new HealthDeclarationAnswerEntity();
        $this->model        = new HealthDeclarationQuestion();
    }


    public function getQuestionByTypeId($typeId, $relations = [])
    {
        return $this->model->where('type_id', $typeId)->with($relations)->get()->toArray();
    }

    public function transformQuestion($question, $studentResults = [])
    {
        $question['student_answers'] = collect($studentResults)->pluck('answer_id')->toArray();
        return $question;
    }


}
