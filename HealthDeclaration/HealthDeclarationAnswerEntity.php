<?php
/**
 *File name : AccessRoleEntity.php / Date: 1/10/2022 - 5:11 PM
 *Code Owner: Thanhnt/ Email: Thanhnt@omt.com.vn/ Phone: 0384428234
 */


namespace App\Entities\HealthDeclaration;


use App\Models\HealthDeclaration\HealthDeclarationAnswer;
use Illuminate\Support\Facades\Config;

class HealthDeclarationAnswerEntity
{

    protected $model;

    public function __construct()
    {
        $this->model = new HealthDeclarationAnswer();
    }


    public function getQuestionByTypeId($typeId, $relations = [])
    {
        return $this->model->where('type_id', $typeId)->with($relations)->get();
    }

    public function transformAnswer($answer)
    {
        dd($answer);
        return [''];
    }

}
