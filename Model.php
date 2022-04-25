<?php

namespace app\core;
use \PDO;

abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_UNIQUE = 'uniq';
    public const RULE_MATCH = 'match';
    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

    }

    abstract public function rules():array;
    public array $errors = [];

    public function labels():array
    {
        return [];
    }

    public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }
    public function validate()
    {
        foreach ($this->rules() as $attribute=>$rules)
        {
            $value=$this->{$attribute};
            foreach ($rules as $rule)
            {
                $rulename=$rule;
                if(!is_string($rulename)){
                    $rulename=$rule[0];

                }
                if($rulename===self::RULE_REQUIRED && empty($value))
                {
                    $this->addErrorRule($attribute, self::RULE_REQUIRED);
                }
                if($rulename===self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL))
                {
                    $this->addErrorRule($attribute, self::RULE_EMAIL);
                }
                if($rulename===self::RULE_MIN && mb_strlen($value)<$rule['min'])
                {
                    $this->addErrorRule($attribute, self::RULE_MIN,$rule);
                }
                if($rulename===self::RULE_MAX && mb_strlen($value)>$rule['max'])
                {
                    $this->addErrorRule($attribute, self::RULE_MAX,$rule);
                }
                if($rulename===self::RULE_UNIQUE)
                {
                    $className=$rule['class'];
                    $uniqueAttr=$rule['attribute'] ?? $attribute;
                    $tableName=$className::tableName();
                    //TODO We have to BIND the value to the query
                    $sql="SELECT * FROM $tableName WHERE $uniqueAttr=:attr";
                    $statement=Application::$app->db->prepare($sql);
                        $statement->bindValue(':attr',$value);
                    $statement->execute();
                    $record=$statement->fetchObject();
                    if($record){
                        $this->addErrorRule($attribute,self::RULE_UNIQUE,['field'=>$this->getLabel($attribute)]);
                    }
                }
                if($rulename===self::RULE_MATCH && $value!=$this->{$rule['match']})
                {
                    $rule['match']=$this->getLabel($rule['match']);
                    $this->addErrorRule($attribute, self::RULE_MATCH,$rule);
                }
            }

        }
        return empty($this->errors);
    }
    public function hasError($attribute)
    {
        return $this->errors[$attribute] ?? false;
    }
    public function getFirstError($attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }

    public function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][]=$message;
    }

    private function addErrorRule(string $attribute, string $rule, $params=[])
    {
        $message=$this->errorMessages()[$rule] ?? '';
        foreach ($params as $key=>$value)
        {
            $message=str_replace("{{$key}}",$value,$message);
        }
        $this->errors[$attribute][]=$message;
    }
    public function errorMessages()
    {
        return [
            self::RULE_REQUIRED=>'This field is required',
            self::RULE_EMAIL=>'This field must be a valid email',
            self::RULE_MIN=>'This field must be at least {min} characters long',
            self::RULE_MAX=>'This field must be at most {max} characters long',
            self::RULE_MATCH=>'This field must same as {match}',
            self::RULE_UNIQUE=>'Record with this {field} already exist',
        ];

    }
}