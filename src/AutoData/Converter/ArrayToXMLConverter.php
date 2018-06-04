<?php

namespace AutoData\Converter;

class ArrayToXMLConverter
{
    /**
     * @param array $array
     * @return string
     */
    public static function convert(array $array): string
    {
        $xml = new \SimpleXMLElement('<xml/>');
        self::recursiveArrayToXML($array, $xml);

        return $xml->asXML();
    }

    /**
     * @param string $xmlString
     * @return array
     */
    public static function revert(string $xmlString): array
    {
        return self::recursiveXMLToArray(new \SimpleXMLElement($xmlString));
    }

    /**
     * @param array $array
     * @param \SimpleXMLElement $parentNode
     */
    private static function recursiveArrayToXML(array $array, \SimpleXMLElement $parentNode): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $node = $parentNode->addChild($key);
                self::recursiveArrayToXML($value, $node);
            } else {
                $parentNode->addChild($key, $value);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $node
     * @param array $out
     * @return array
     */
    private static function recursiveXMLToArray(\SimpleXMLElement $node): array
    {
        $out = [];
        $idx = null;
        $prevKey = null;
        if (count($node->children()) > 1) {
            $idx = 0;
        }
        foreach ($node->children() as $currentKey => $child) {
            $key = $currentKey;
            if ($currentKey === $prevKey) {
                // Sibling element with identical key
                $key .= '_' . ++$idx;
            }
            $prevKey = $key;
            $out[$key] = count($child->children()) ? self::recursiveXMLToArray($child) : \trim((string)$child);
        }
        unset($idx);
        return $out;
    }
}
