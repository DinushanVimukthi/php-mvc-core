<?php

namespace stdin\core\form;

use stdin\core\Model;

class Form{

    public static function begin($action,$method): Form
    {
        echo sprintf('<form action="%s" method="%s">',$action,$method);
        return new Form();
    }
    public static function end()
    {
        echo '</form>';
    }
    public function TextField(Model $model, $attribute): InputField
    {
        return new InputField($model,$attribute);
    }

    public function TextAreaField(Model $model, $attribute): TextAreaField
    {
        return new TextAreaField($model,$attribute);
    }



}