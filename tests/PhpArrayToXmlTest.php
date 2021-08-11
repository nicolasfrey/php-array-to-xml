<?php

use PHPUnit\Framework\TestCase;
use RefactorStudio\PhpArrayToXml\PhpArrayToXml;

class PhpArrayToXmlTest extends TestCase
{
    /**
     * @param $name
     * @return string
     */
    protected function getXmlStub($name)
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $name . '.xml');
    }

    /**
     * @param $name
     * @return array
     */
    protected function getArrayStub($name)
    {
        return include(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $name . '.php');
    }

    /** @test */
    public function check_if_custom_helper_methods_actually_work()
    {
        $array = $this->getArrayStub('test_default');
        $xml = $this->getXmlStub('test_default');

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('street', $array['address']);

        $this->assertTrue(is_string($xml));
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
    }

    /** @test */
    public function check_if_every_stub_is_being_tested()
    {
        $missing = null;
        $stubs = glob(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . '*.*');

        foreach($stubs as $stub) {
            $path_info = pathinfo($stub);
            $method_name = $path_info['filename'];

            if(!method_exists($this, $method_name)) {
                $missing .= "\n Method name missing: {$method_name}() for stub: {$stub}";
            }
        }
        $this->assertEmpty($missing);
    }

    /** @test */
    public function check_if_all_constants_are_available()
    {
        $this->assertEquals('lowercase', PhpArrayToXml::LOWERCASE);
        $this->assertEquals('uppercase', PhpArrayToXml::UPPERCASE);
    }

    public function test_default_values()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_version_encoding()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setVersion('1.1')->setEncoding('UTF-16')->prettify()->toXmlString($array);

        $this->assertEquals(
            mb_convert_encoding($expected, 'UTF-8', 'UTF-16'),
            mb_convert_encoding($result, 'UTF-8', 'UTF-16')
        );
    }

    public function test_default()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_format_output()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setFormatOutput()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_prettify() // Alias
    {
        $array = $this->getArrayStub('test_format_output');
        $expected = $this->getXmlStub('test_format_output');

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_custom_root_name()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setCustomRootName('MyCustomRootName')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_custom_root_name_exception()
    {
        $this->expectException(Exception::class);

        (new PhpArrayToXml)->setCustomRootName('@@ invalid root name @@');
    }

    public function test_custom_tag_name()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setCustomTagName('MyCustomTag')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_custom_tag_name_exception()
    {
        $this->expectException(Exception::class);

        (new PhpArrayToXml)->setCustomTagName('@@ invalid tag name @@');
    }

    public function test_separator()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_separator_triple_underscores()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setSeparator('___')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    // Note: An xml tag cannot start with a sign (or other invalid) char, the tag will be prefixed by an underscore (check xml)
    public function test_separator_invalid_start_char()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setSeparator('-')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_keys_null()
    {
        $array = $this->getArrayStub('test_format_output');
        $expected = $this->getXmlStub('test_format_output');

        $result = (new PhpArrayToXml)->setTransformTags(null)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_keys_lowercase()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setTransformTags(PhpArrayToXml::LOWERCASE)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_keys_lowercase_except_custom()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)
            ->setCustomRootName('MyCustomRootName')
            ->setCustomTagName('MyCustomTagName')
            ->setTransformTags(PhpArrayToXml::LOWERCASE)
            ->setFormatOutput()
            ->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_keys_uppercase()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setTransformTags(PhpArrayToXml::UPPERCASE)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_transform_keys_uppercase_except_custom()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)
            ->setCustomRootName('MyCustomRootName')
            ->setCustomTagName('MyCustomTagName')
            ->setTransformTags(PhpArrayToXml::UPPERCASE)
            ->prettify()
            ->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_numeric_tag_suffix()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setNumericTagSuffix('_')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_numeric_tag_suffix_fixed_keys()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setNumericTagSuffix(true)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_numeric_tag_suffix_custom()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)
            ->setCustomTagName('MyCustomTagName')
            ->setNumericTagSuffix('')
            ->prettify()
            ->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_auto_encode_special_value_chars()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_cdata()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_invalid_tag_name()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_attributes()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_attributes_and_cdata()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_cast_boolean_values()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setCastBooleanValueTrue('Yes')->setCastBooleanValueFalse('No')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }

    public function test_cast_null_values()
    {
        $array = $this->getArrayStub(__FUNCTION__);
        $expected = $this->getXmlStub(__FUNCTION__);

        $result = (new PhpArrayToXml)->setCastNullValue('__NULL__')->prettify()->toXmlString($array);

        $this->assertEquals($expected, $result);
    }
}
