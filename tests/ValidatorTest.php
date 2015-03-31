<?php
namespace tests;

use samsonframework\w3c\Validator;

/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 04.08.14 at 16:42
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validator */
    protected $validator;

    public function testValidValidation()
    {
        $this->validator = new Validator('example.com');
        $this->validator->validate();
    }

    public function testInvalidValidation()
    {
        $this->validator = new Validator('https://raw.githubusercontent.com/samsonframework/w3c/master/tests/invalid.html');
        $this->validator->validate();
    }
}
