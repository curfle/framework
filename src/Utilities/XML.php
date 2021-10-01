<?php

namespace Curfle\Utilities;

use SimpleXMLElement;

class XML
{
    /**
     * Parses an XML string into an array or object.
     *
     * @param string $xml
     * @param bool $toArray
     * @return array|object|null
     */
    public static function parse(string $xml, bool $toArray = true): array|object|null
    {
        $xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(JSON::from($xml), $toArray);
    }

    /**
     * Generates an XML string from an array or object.
     *
     * @param array $data
     * @param string $root
     * @return string
     */
    public static function from(array $data, string $root = "data"): string
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$root></$root>");
        static::arrayToXML($data, $xml);
        return $xml->asXML();
    }

    /**
     * Transforms array data into a SimpleXMLElement.
     *
     * @param array $data
     * @param SimpleXMLElement $xml_data
     */
    private static function arrayToXML(array $data, SimpleXMLElement &$xml_data)
    {
        foreach ($data as $key => $value) {
            // check if key is numeric
            if (is_numeric($key))
                $key = "item" . $key;

            // check if is leaf node or has childs
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                static::arrayToXML($value, $subnode);
            } else {
                $xml_data->addChild((string)$key, htmlspecialchars((string)$value));
            }
        }
    }
}