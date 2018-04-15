<?php

namespace RefactorStudio\PhpArrayToXml;

use DOMDocument;
use DOMElement;

class PhpArrayToXml
{
    const LOWERCASE = 'lowercase';
    const UPPERCASE = 'uppercase';

    protected $_doc;
    protected $_version = '1.0';
    protected $_encoding = 'UTF-8';
    protected $_default_root_name = 'root';
    protected $_custom_root_name = null;
    protected $_default_node_name = 'node';
    protected $_custom_node_name = null;
    protected $_separator = '_';
    protected $_transform_key_names = null;
    protected $_format_output = false;
    protected $_numeric_node_suffix = null;

    /**
     * Set the version of the XML (Default = '1.0')
     *
     * @param string $value
     * @return PhpArrayToXml
     */
    public function setVersion($value = '1.0')
    {
        $this->_version = $value;

        return $this;
    }

    /**
     * Get the version of the XML
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Set the encoding of the XML (Default = 'UTF-8')
     *
     * @param string $value
     * @return PhpArrayToXml
     */
    public function setEncoding($value = 'UTF-8')
    {
        $this->_encoding = $value;

        return $this;
    }

    /**
     * Get the encoding of the XML
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set the format output of the XML
     *
     * @param bool $value
     * @return PhpArrayToXml
     */
    public function setFormatOutput($value = true)
    {
        $this->_format_output = ($value === true ? true : false);

        return $this;
    }

    /**
     * Alias for setFormatOutput(true)
     *
     * @return PhpArrayToXml
     */
    public function prettify()
    {
        $this->setFormatOutput(true);

        return $this;
    }

    /**
     * Get the format output of the XML
     *
     * @return bool
     */
    public function getFormatOutput()
    {
        return $this->_format_output;
    }

    /**
     * Set the custom root name of the XML
     *
     * @param $value
     * @return PhpArrayToXml
     * @throws \Exception
     */
    public function setCustomRootName($value)
    {
        if (!$this->isValidNodeName($value)) {
            throw new \Exception('Not a valid root name: ' . $value);
        }

        $this->_custom_root_name = $value;

        return $this;
    }

    /**
     * Get the custom root name of the XML
     *
     * @return string
     */
    public function getCustomRootName()
    {
        return $this->_custom_root_name;
    }

    /**
     * Get the default root name of the XML
     *
     * @return string
     */
    public function getDefaultRootName()
    {
        return $this->_default_root_name;
    }

    /**
     * Set the custom node name of the XML (only used for inner arrays)
     *
     * @param $value
     * @return PhpArrayToXml
     * @throws \Exception
     */
    public function setCustomNodeName($value)
    {
        if (!$this->isValidNodeName($value)) {
            throw new \Exception('Not a valid node name: ' . $value);
        }

        $this->_custom_node_name = $value;

        return $this;
    }

    /**
     * Get the custom node name of the XML (only used for inner arrays)
     *
     * @return string
     */
    public function getCustomNodeName()
    {
        return $this->_custom_node_name;
    }

    /**
     * Get the default node name of the XML (only used for inner arrays)
     *
     * @return string
     */
    public function getDefaultNodeName()
    {
        return $this->_default_node_name;
    }

    /**
     * Set the value for the separator that will be used to replace special characters in node/tag names
     *
     * @param $value
     * @return PhpArrayToXml
     */
    public function setSeparator($value)
    {
        $this->_separator = $value;

        return $this;
    }

    /**
     * Get the value for the separator that will be used to replace special characters in node/tag names
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Set the method for transforming keys
     * Possible values:
     * - null
     * - 'uppercase'
     * - 'lowercase'
     *
     * @param $value
     * @return PhpArrayToXml
     */
    public function setMethodTransformKeys($value = null)
    {
        switch($value) {
            case self::LOWERCASE:
            case self::UPPERCASE: {
                $this->_transform_key_names = $value;
                break;
            }
            default: {
                if($value === null) {
                    $this->_transform_key_names = null;
                }
            }
        }

        return $this;
    }

    /**
     * Get the method for transforming keys
     *
     * @return string
     */
    public function getMethodTransformKeys()
    {
        return $this->_transform_key_names;
    }

    /**
     * Set the numeric node suffix
     *
     * @param null $value
     * @return PhpArrayToXml
     */
    public function setNumericNodeSuffix($value = null)
    {
        $this->_numeric_node_suffix = $value;

        if($value === true || $value === false) {
            $this->_numeric_node_suffix = '';
        }
        return $this;
    }

    /**
     * Get the numeric node suffix
     *
     * @return null
     */
    public function getNumericNodeSuffix()
    {
        return $this->_numeric_node_suffix;
    }

    /**
     * Validate if a given value has a proper node/tag starting character to be used in XML
     *
     * @param null $value
     * @return bool
     */
    public static function hasValidNodeStart($value = null)
    {
        if(preg_match(self::getValidXmlNodeStartPattern(), $value) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Validate if a given value is a valid node/tag character
     *
     * @param null $value
     * @return bool
     */
    public static function isValidNodeNameChar($value = null)
    {
        if(preg_match(self::getValidXmlNodeNameChar(), $value) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Validate if a given value is a proper node/tag name to be used in XML
     *
     * @param null $value
     * @return bool
     */
    public static function isValidNodeName($value = null)
    {
        if(empty($value) || is_int($value)) {
            return false;
        }

        if(preg_match(self::getValidXmlNodeNamePattern(), $value) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Convert an array to XML
     *
     * @param array $array
     * @return string
     */
    public function toXmlString($array = [])
    {
        $this->_doc = new DOMDocument($this->getVersion(), $this->getEncoding());
        $this->_doc->formatOutput = $this->getFormatOutput();

        $root = $this->_doc->createElement($this->createValidRootName($this->getCustomRootName()));

        $this->_doc->appendChild($root);

        $this->addArrayElements($root, $array);

        return $this->_doc->saveXML();
    }

    /**
     * Get a regex pattern for valid node names
     *
     * @return string
     */
    protected static function getValidXmlNodeNamePattern()
    {
        return '~
            # XML 1.0 Name symbol PHP PCRE regex <http://www.w3.org/TR/REC-xml/#NT-Name>
            (?(DEFINE)
                (?<NameStartChar> [:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}])
                (?<NameChar>      (?&NameStartChar) | [.\\-0-9\\xB7\\x{0300}-\\x{036F}\\x{203F}-\\x{2040}])
                (?<Name>          (?&NameStartChar) (?&NameChar)*)
            )
            ^(?&Name)$
            ~ux';
    }

    /**
     * Get a regex pattern for valid node chars
     *
     * @return string
     */
    protected static function getValidXmlNodeNameChar()
    {
        return '~
            (?(DEFINE)
                (?<NameStartChar> [:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}])
                (?<NameChar>      (?&NameStartChar) | [.\\-0-9\\xB7\\x{0300}-\\x{036F}\\x{203F}-\\x{2040}])
            )
            ^(?&NameChar)$
            ~ux';
    }

    /**
     * Get a regex pattern for valid node starting characters
     *
     * @return string
     */
    protected static function getValidXmlNodeStartPattern()
    {
        return '~^([:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}])~ux';
    }

    /**
     * Converts arrays to DOMDocument elements
     *
     * @param DOMElement $parent
     * @param array $array
     */
    protected function addArrayElements(DOMElement $parent, $array = [])
    {
        if (is_array($array)) {
            foreach ($array as $name => $value) {
                if (!is_array($value)) {
                    // Create an XML element
                    $node = $this->createElement($name, $value);
                    $parent->appendChild($node);
                } else {

                    if(array_key_exists('@value', $value)) {
                        $cdata = array_key_exists('@cdata', $value) && $value['@cdata'] === true ? true : false;
                        $attributes = array_key_exists('@attr', $value) && is_array($value['@attr']) ? $value['@attr'] : [];

                        if(!is_array($value['@value'])) {
                            // Create an XML element
                            $node = $this->createElement($name, $value['@value'], $cdata, $attributes);
                            $parent->appendChild($node);
                        } else {
                            // Create an empty XML element 'container'
                            $node = $this->createElement($name, null);

                            foreach($attributes as $attribute_name => $attribute_value) {
                                $node->setAttribute($attribute_name, $attribute_value);
                            }

                            $parent->appendChild($node);

                            // Add all the elements within the array to the 'container'
                            $this->addArrayElements($node, $value['@value']);
                        }
                    }
                    else {
                        // Create an empty XML element 'container'
                        $node = $this->createElement($name, null);
                        $parent->appendChild($node);

                        // Add all the elements within the array to the 'container'
                        $this->addArrayElements($node, $value);
                    }
                }
            }
        }
    }

    /**
     * See if a value matches an integer (could be a integer within a string)
     *
     * @param $value
     * @return bool
     */
    protected function isNumericKey($value)
    {
        $pattern = '~^(0|[1-9][0-9]*)$~ux';

        return preg_match($pattern, $value) === 1;
    }

    /**
     * Creates an element for DOMDocument
     *
     * @param $name
     * @param null $value
     * @param bool $cdata
     * @param array $attributes
     * @return DOMElement
     */
    protected function createElement($name, $value = null, $cdata = false, $attributes = [])
    {
        $name = $this->createValidNodeName($name);

        if($cdata === true) {
            $element = $this->_doc->createElement($name);
            $element->appendChild($this->_doc->createCDATASection($value));

            foreach($attributes as $attribute_name => $attribute_value) {
                $element->setAttribute($attribute_name, $attribute_value);
            }

            return $element;
        }

        $element = $this->_doc->createElement($name, $value);

        foreach($attributes as $attribute_name => $attribute_value) {
            $element->setAttribute($attribute_name, $attribute_value);
        }

        return $element;
    }

    /**
     * Creates a valid node name
     *
     * @param null $name
     * @return string
     */
    protected function createValidNodeName($name = null)
    {
        if(empty($name) || $this->isNumericKey($name)) {
            $key = $name;

            if ($this->isValidNodeName($this->getCustomNodeName())) {
                $name = $this->getCustomNodeName();
            } else {
                $name = $this->transformNodeName($this->getDefaultNodeName());
            }

            if($this->getNumericNodeSuffix() !== null) {
                $name = $name . (string)$this->getNumericNodeSuffix() . $key;
            }
            return $name;
        }

        if(!$this->isValidNodeName($name)) {
            $name = $this->replaceInvalidNodeChars($name);

            if(!self::hasValidNodeStart($name)) {
                $name = $this->prefixInvalidNodeStartingChar($name);
            }
        }
        return $this->transformNodeName($name);
    }

    /**
     * If a node has an invalid starting character, use an underscore as prefix
     *
     * @param $value
     * @return string
     */
    protected function prefixInvalidNodeStartingChar($value)
    {
        return '_' . substr($value, 1);
    }

    /**
     * Replace invalid node characters
     *
     * @param $value
     * @return null|string|string[]
     */
    protected function replaceInvalidNodeChars($value)
    {
        $pattern = '';
        for($i=0; $i < strlen($value); $i++) {
            if(!self::isValidNodeNameChar($value[$i])) {
                $pattern .= "\\$value[$i]";
            }
        }

        if(!empty($pattern)) {
            $value = preg_replace("/[{$pattern}]/", $this->getSeparator(), $value);
        }
        return $value;
    }

    /**
     * Creates a valid root name
     *
     * @param null $name
     * @return string
     */
    protected function createValidRootName($name = null)
    {
        if (is_string($name)) {
            $name = preg_replace("/[^_a-zA-Z0-9]/", $this->getSeparator(), $name);
        }
        if ($this->isValidNodeName($name)) {
            return $name;
        }
        return $this->transformNodeName($this->getDefaultRootName());
    }

    /**
     * Transforms a node name (only when specified)
     *
     * @param null $name
     * @return null|string
     */
    protected function transformNodeName($name = null)
    {
        switch($this->getMethodTransformKeys()) {
            case self::LOWERCASE: {
                return strtolower($name);
                break;
            }
            case self::UPPERCASE: {
                return strtoupper($name);
                break;
            }
            default: {
                return $name;
            }
        }
    }
}
