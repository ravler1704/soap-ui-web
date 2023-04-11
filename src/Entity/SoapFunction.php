<?php

namespace App\Entity;

class SoapFunction
{
    public string $methodName;
    public array $paramList;

    public function __construct($typeStr)
    {
        $regexMethodName = '\b(?!struct\b)\w+';
        preg_match('/'.$regexMethodName.'/', $typeStr, $matchesMethodName);
        $this->methodName = $matchesMethodName[0];
        $regexParams = '\n\s*(\w+)\s*(\w+)';
        preg_match_all('/'.$regexParams.'/', $typeStr, $params);
        $paramTypes = $params[1];
        $paramNames = $params[2];
        $this->paramList = []; //init prop
        foreach ($paramTypes as $key => $type) {
            $this->paramList[] = [
                'type' => $type,
                'name' => $paramNames[$key],
                'value' => ''
            ];
        }
    }

    public function __toArray(): array
    {
        return [
            'methodName' => $this->methodName,
            'paramList' => $this->paramList
        ];
    }
}