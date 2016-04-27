<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
namespace SketchbookCafe\GetSettingName;

use SketchbookCafe\SBC\SBC as SBC;

class GetSettingName
{
    private $value = '';

    // Construct
    public function __construct($value)
    {
        $method = 'GetSettingName->__construct()';

        // Quick Clean
        $value = isset($value) ? trim(addslashes($value)) : '';

        // Lowercase
        $value = strtolower($value);

        // Length Check
        if (isset($value{60}))
        {
            SBC::userError('Invalid Setting Name');
        }

        // Letters and Underscores Only
        if (preg_match('/[^a-z_]/',$value))
        {
            SBC::userError('Setting Name may only contain letters a-z and underscores');
        }

        // Set
        $this->value = $value;
    }

    // Get Value
    final public function getValue()
    {
        $method = 'GetSettingName->getValue()';

        return $this->value;
    }
}