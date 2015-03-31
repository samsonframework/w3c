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

    /** @var string Invalid URL for testing */
    protected $invalidUrl = 'https://raw.githubusercontent.com/samsonframework/w3c/master/tests/invalid.html';

    /**
     * @expectedException \samsonframework\w3c\ParseException
     */
    public function testInvalidRequest()
    {
        $this->validator = new Validator($this->invalidUrl, 'http://validator.w3.org/');
        $this->validator->validate();
    }

    public function testValidValidation()
    {
        $this->validator = new Validator('example.com');
        $this->validator->validate();
    }

    public function testInvalidValidation()
    {
        $this->validator = new Validator($this->invalidUrl);
        $this->validator->validate();
    }
}
