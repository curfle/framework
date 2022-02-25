<?php

namespace Curfle\Utilities;

use Curfle\Support\Arr;
use SimpleXMLElement;

class XML
{
    /**
     * Parses an XML string into an array or object.
     *
     * @param string $xml
     * @param null $prefix
     * @param null $namespace
     * @param bool $toArray
     * @return array|object|null
     */
    public static function parse(string $xml, $prefix = null, $namespace = null, bool $toArray = false): array|object|null
    {
        $xml = simplexml_load_string(
            $xml,
            "SimpleXMLElement",
            LIBXML_NOCDATA,
            $namespace ?? $prefix ?? "",
            $prefix !== null);

        // convert to array if forced
        if ($toArray)
            return json_decode(JSON::from($xml), true);

        return $xml;
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
    private static function arrayToXML(array $data, SimpleXMLElement $xml_data)
    {
        foreach ($data as $key => $value) {
            // check if $value is array and all nodes are leafs
            $allNodesAreLeafs = true;
            if (Arr::is($value)) {
                foreach ($value as $inner_value)
                    if (Arr::is($inner_value))
                        $allNodesAreLeafs = false;
            }else{
                $allNodesAreLeafs = false;
            }

            // handle array
            if($allNodesAreLeafs){
                foreach($value as $inner_value){
                    $xml_data->addChild((string)$key, htmlspecialchars((string)$inner_value));
                }
            }else{
                // check if key is numeric
                if (is_numeric($key))
                    $key = "item$key";

                // check if is leaf node or has childs
                if (Arr::is($value)) {
                    $subnode = $xml_data->addChild($key);
                    static::arrayToXML($value, $subnode);
                } else {
                    $xml_data->addChild((string)$key, htmlspecialchars((string)$value));
                }
            }
        }
    }
}