<?php
declare(strict_types=1);

namespace LatteView\Extension\Frontend\Serializers;

use JsonSerializable;

final class UniversalSerializer
{
    /**
     * Serialize data to JSON string.
     *
     * @param mixed $data Data to serialize.
     * @return string JSON string.
     */
    public static function serialize(mixed $data): string
    {
        $serialized = self::prepareData($data);

        return json_encode($serialized, JSON_THROW_ON_ERROR | JSON_HEX_TAG);
    }

    /**
     * Prepare data for serialization.
     *
     * @param mixed $data Data to prepare.
     * @param int $depth Current recursion depth.
     * @return mixed Prepared data.
     */
    private static function prepareData(mixed $data, int $depth = 0): mixed
    {
        // Prevent infinite recursion
        if ($depth > 10) {
            return null;
        }

        // Handle objects with toArray() method (CakePHP entities, collections)
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }

        // Handle JsonSerializable objects
        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        // Handle arrays
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = self::prepareData($value, $depth + 1);
            }

            return $result;
        }

        // Handle generic objects
        if (is_object($data)) {
            $result = [];
            foreach (get_object_vars($data) as $key => $value) {
                $result[$key] = self::prepareData($value, $depth + 1);
            }

            return $result;
        }

        // Handle scalar values - only wrap if they're not part of an array context
        if (is_scalar($data) || is_null($data)) {
            // If this is a top-level scalar (depth 0), wrap it for consistency
            // Otherwise, return as-is for array/object context
            return $depth === 0 ? ['data' => $data] : $data;
        }

        return $data;
    }
}
