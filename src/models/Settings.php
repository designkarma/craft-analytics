<?php

namespace designkarma\analytics\models;

use craft\base\Model;

class Settings extends Model
{
    public ?string $propertyId = null;
    public ?string $credentialsPath = null;

    protected function defineRules(): array
    {
        return [
            [['propertyId', 'credentialsPath'], 'string'],
            [['propertyId', 'credentialsPath'], 'required'],
        ];
    }
}