<?php
namespace App\Helpers;

class JsonHelper
{
    public static function traverseJson($data, $path = '')
    {
        $nodes = [];

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Skip unnecessary metadata keys
                if (in_array($key, ['_type', '_timestamp', '_writable', '_object'])) {
                    continue;
                }

                // Build the full path
                $newPath = $path ? $path . '.' . $key : $key;

                if (is_array($value)) {
                    // If it contains `_value`, treat it as a parameter (leaf node)
                    if (isset($value['_value'])) {
                        $nodes[$key] = [
                            'is_object' => false,
                            'value' => $value['_value'], // Display the actual value
                            'path' => $newPath, // The path without `_value`
                            'writable' => $value['_writable'] ?? false,
                            'type' => $value['_type'] ?? 'string', // Default to 'string' if not present
                        ];
                    } else {
                        // Treat it as an object (container)
                        $nodes[$key] = [
                            'is_object' => true,
                            'value' => null, // No direct value for containers
                            'path' => $newPath,
                            'writable' => false, // Containers are not writable
                            'type' => 'object', // Type is 'object' for containers
                            'children' => self::traverseJson($value, $newPath), // Recursively process children
                        ];
                    }
                } else {
                    // Leaf node (direct value)
                    $nodes[$key] = [
                        'is_object' => false,
                        'value' => $value,
                        'path' => $newPath,
                        'writable' => false,
                        'type' => gettype($value), // Derive type from the value
                    ];
                }
            }
        }

        return $nodes;
    }
}