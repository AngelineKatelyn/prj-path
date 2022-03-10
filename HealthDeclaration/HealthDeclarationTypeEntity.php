<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\HealthDeclarationType;
use App\Models\HealthDeclaration\StudentHealthResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class HealthDeclarationTypeEntity
{

    protected $cacheKeys;
    protected $cacheKeysGetTypes;
    protected $model;
    protected $questionEntity;
    protected $answerEntity;
    protected $heightWeightEntity;

    public function __construct()
    {
        $this->cacheKeys          = Config::get('cache_keys.health_declaration');
        $this->cacheKeysGetTypes  = $this->cacheKeys['types'];
        $this->model              = new HealthDeclarationType();
        $this->questionEntity     = new HealthDeclarationQuestionEntity();
        $this->answerEntity       = new HealthDeclarationAnswerEntity();
        $this->heightWeightEntity = new StudentHeightWeightEntity();
    }

    public function getAllParentTypes($student)
    {
        $types = Cache::get($this->cacheKeysGetTypes);
        $types = is_null($types) ? $this->updateGetAllTypesCache() : $types;
        return collect($types)->transform(function($type) use ($student) {
            $type['is_disable'] = !checkStudentHasBmi($student);
            $type['message']    = $type['is_disable'] ? trans('general.student_has_not_bmi') : "";
            return $type;
        });
    }

    public function updateGetAllTypesCache()
    {
        return $this->model->all();
    }


    public function getTypeInfo($typeId, $relation = [])
    {
        return $this->model->where('id', $typeId)->with($relation)->first()->toArray();
    }

    public function getQuestionHeightWeight($type, $student, Carbon $date)
    {
        $results = $this->getBmiResultWithHeightWeight($student);
        foreach ($results as $key => $item) {
            $type[$key] = $item;
        }
        return $type;
    }

    public function getTypesHeathDeclaration(
        $types = [
            HEALTH_DECLARATION_POWER,
            HEALTH_DECLARATION_NUTRITION,
            HEALTH_DECLARATION_SMOKE,
            HEALTH_DECLARATION_WINE,
            HEALTH_DECLARATION_NERVE,
        ]
    ) {
        return $this->model->whereIn('id', $types)
            ->with('questions', 'questions.answers', 'questionKey', 'questionKey.answers')
            ->get()
            ->toArray();
    }

    public function getQuestionAnswerType($type, $studentId, Carbon $date)
    {
        $questions         = $type['questions'] ?? $this->questionEntity->getQuestionByTypeId($type['id'],
                ['answers']);
        $questionIds       = collect($questions)->pluck('id')->toArray();
        $studentResults    = $this->getStudentHealthResultsByQuestionIds($questionIds, $studentId);
        $type['questions'] = collect($questions)->transform(function($question, $index) use (
            $studentResults,
            $questions
        ) {
            $studentResults = $studentResults->where('question_id', $question['id'])->toArray();
            $countQuestions = count($questions);
            $question       = $this->questionEntity->transformQuestion($question, $studentResults);
            if (count($question['answers']) > 0) {
                $question['answers'] = collect($question['answers'])->transform(function($answer) use (
                    $countQuestions,
                    $index,
                    $questions
                ) {
                    if (in_array($answer['question_id'], [4, 12, 13])) {
                        $answer['child_id'] = 0;
                    } else {
                        $answer['child_id'] = $answer['question_child_id'] ?? ($countQuestions > 1 ? ($index + 1 == $countQuestions ? 0 : $questions[$index + 1]['id']) : 0);
                    }
                    return $answer;
                })->toArray();
            }
            return $question;
        })->toArray();
        return $type;
    }

    private function getStudentHealthResultsByQuestionIds($questionIds, $studentId)
    {
        return StudentHealthResult::whereIn('question_id', $questionIds)
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->unique('question_id');
    }

    public function getBmiResultWithHeightWeight($student)
    {
        $studentHeightWeight          = $this->heightWeightEntity->getRecordStudentHeightWeight($student['id']);
        $result['student_height']     = $studentHeightWeight['height'] ?? null;
        $result['student_weight']     = $studentHeightWeight['weight'] ?? null;
        $result['student_bmi_int']    = calculateBmi($result['student_height'], $result['student_weight']);
        $heathBmi                     = (new HealthStudentInfoBmiEntity())->getBmiForStudent($student,
            $result['student_bmi_int']);
        $result['bmi_name']           = $heathBmi['name'] ?? null;
        $result['evaluation_results'] = $heathBmi['evaluation_results'] ?? null;
        $result['advices']            = $heathBmi['advices'] ?? null;
        $result['color']              = $heathBmi['color'] ?? null;
        return $result;
    }


}
