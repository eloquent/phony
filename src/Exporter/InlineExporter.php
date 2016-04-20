<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Exporter;

use Eloquent\Phony\Mock\MockInterface;
use Exception;
use SplObjectStorage;
use Throwable;

/**
 * Exports values to inline strings.
 */
class InlineExporter implements ExporterInterface
{
    /**
     * Get the static instance of this exporter.
     *
     * @return ExporterInterface The static exporter.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(1, true);
        }

        return self::$instance;
    }

    /**
     * Construct a new inline exporter.
     *
     * @param integer $depth        The depth.
     * @param boolean $incrementIds True if IDs should increment. Used for testing purposes.
     */
    public function __construct($depth, $incrementIds)
    {
        $this->incrementIds = $incrementIds;
        $this->depth = $depth;
        $this->objectIds = array();
        $this->objectId = 0;
        $this->jsonFlags = 0;

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $this->jsonFlags |= JSON_UNESCAPED_SLASHES;
        }
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $this->jsonFlags |= JSON_UNESCAPED_UNICODE;
        }
    }

    /**
     * Set the default depth.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param integer $depth The depth.
     *
     * @return integer The previous depth.
     */
    public function setDepth($depth)
    {
        $oldDepth = $this->depth;
        $this->depth = $depth;

        return $oldDepth;
    }

    /**
     * Export the supplied value.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param mixed        &$value The value.
     * @param integer|null $depth  The depth, or null to use the default.
     *
     * @return string The exported value.
     */
    public function export(&$value, $depth = null)
    {
        if (null === $depth) {
            $depth = $this->depth;
        }

        $final = (object) array();
        $stack = array(array(&$value, $final, 0));
        $results = array();
        $seenObjects = new SplObjectStorage();
        $seenArrays = array();
        $arrayResults = array();
        $arrayId = 0;

        while ($stack) {
            $entry = array_shift($stack);
            $value = &$entry[0];
            $result = $entry[1];
            $currentDepth = $entry[2];
            $results[] = $result;

            switch (gettype($value)) {
                case 'NULL':
                    $result->type = 'null';

                    break;

                case 'boolean':
                    if ($value) {
                        $result->type = 'true';
                    } else {
                        $result->type = 'false';
                    }

                    break;

                case 'integer':
                    $result->type = strval($value);

                    break;

                case 'double':
                    $result->type = sprintf('%e', $value);

                    break;

                case 'resource':
                    $result->type = 'resource#' . intval($value);

                    break;

                case 'string':
                    $result->type = json_encode($value, $this->jsonFlags);

                    break;

                case 'array':
                    if (isset($value[self::ARRAY_ID_KEY])) {
                        $id = $value[self::ARRAY_ID_KEY];
                        $displayId = $id;
                    } else {
                        $id = $value[self::ARRAY_ID_KEY] = '#' . $arrayId++;

                        if ($this->incrementIds) {
                            $displayId = $id;
                        } else {
                            $displayId = '#0';
                        }
                    }

                    $seenArrays[$id] = &$value;
                    $result->type = $displayId;

                    if (isset($arrayResults[$id])) {
                        $result->type .= '[]';

                        break;
                    }

                    if ($depth > -1 && $currentDepth >= $depth) {
                        $count = count($value) - 1;

                        if ($count) {
                            $result->type .= '[:' . $count . ']';
                        } else {
                            $result->type .= '[]';
                        }

                        break;
                    }

                    $arrayResults[$id] = $result;

                    $result->children = array();
                    $result->sequence = true;
                    $sequenceKey = 0;

                    foreach ($value as $key => &$childValue) {
                        if (self::ARRAY_ID_KEY === $key) {
                            continue;
                        }

                        if ($result->sequence) {
                            if ($key !== $sequenceKey++) {
                                $result->map = true;
                                $result->sequence = false;
                            }
                        }

                        $keyResult = (object) array();
                        $valueResult = (object) array();
                        $result->children[] = array($keyResult, $valueResult);

                        $stack[] = array($key, $keyResult, $currentDepth + 1);
                        $stack[] = array(
                            &$childValue,
                            $valueResult,
                            $currentDepth + 1,
                        );
                    }

                    break;

                case 'object':
                    $hash = spl_object_hash($value);

                    if (isset($this->objectIds[$hash])) {
                        $id = $this->objectIds[$hash];
                    } elseif ($this->incrementIds) {
                        $id = $this->objectIds[$hash] = '#' . $this->objectId++;
                    } else {
                        $id = '#0';
                    }

                    if ($seenObjects->contains($value)) {
                        $result->type = $id . '{}';

                        break;
                    }

                    $result->type = get_class($value);
                    $isException = $value instanceof Throwable ||
                        $value instanceof Exception;
                    $phpValues = (array) $value;

                    unset($phpValues["\0gcdata"]);

                    if ($value instanceof MockInterface) {
                        $handleProperty = "\0" . $result->type . "\0_handle";

                        if ($phpValues[$handleProperty]) {
                            $phpValues['phony.label'] =
                                $phpValues[$handleProperty]->label();
                        }

                        unset($phpValues[$handleProperty]);
                    }

                    if ($isException) {
                        unset(
                            $phpValues["\0*\0file"],
                            $phpValues["\0*\0line"],
                            $phpValues["\0Exception\0trace"],
                            $phpValues["\0Exception\0string"],
                            $phpValues['xdebug_message']
                        );
                    }

                    $properties = array();
                    $propertyCounts = array();

                    foreach ($phpValues as $propertyName => $propertyValue) {
                        if (
                            preg_match(
                                '/^\x00([^\x00]+)\x00([^\x00]+)$/',
                                $propertyName,
                                $matches
                            )
                        ) {
                            if (
                                '*' === $matches[1] ||
                                $result->type === $matches[1]
                            ) {
                                $propertyName = $realName = $matches[2];
                            } else {
                                $propertyName = $matches[2];
                                $realName = $matches[1] . '.' . $propertyName;
                            }

                            $properties[] = array(
                                $propertyName,
                                $realName,
                                $propertyValue,
                            );
                        } else {
                            $properties[] = array(
                                $propertyName,
                                $propertyName,
                                $propertyValue,
                            );
                        }

                        if (isset($propertyCounts[$propertyName])) {
                            $propertyCounts[$propertyName] += 1;
                        } else {
                            $propertyCounts[$propertyName] = 1;
                        }
                    }

                    $values = array();

                    foreach ($properties as $property) {
                        list($shortName, $realName, $propertyValue) = $property;

                        if ($propertyCounts[$shortName] > 1) {
                            $values[$realName] = $propertyValue;
                        } else {
                            $values[$shortName] = $propertyValue;
                        }
                    }

                    if ($isException) {
                        if ('' === $values['message']) {
                            unset($values['message']);
                        }
                        if (0 === $values['code']) {
                            unset($values['code']);
                        }
                        if (!$values['previous']) {
                            unset($values['previous']);
                        }
                    }

                    if ('stdClass' === $result->type) {
                        $result->type = '';
                    }

                    $result->type .= $id;

                    if ($depth > -1 && $currentDepth >= $depth) {
                        if ($values) {
                            $result->type .= '{:' . count($values) . '}';
                        } else {
                            $result->type .= '{}';
                        }

                        break;
                    }

                    $seenObjects->offsetSet($value, $result);

                    $result->children = array();
                    $result->object = true;

                    foreach ($values as $key => &$childValue) {
                        $valueResult = (object) array();
                        $result->children[] = array($key, $valueResult);

                        $stack[] = array(
                            &$childValue,
                            $valueResult,
                            $currentDepth + 1,
                        );
                    }

                    break;

                default:
                    $result->type = '???';
            }
        }

        foreach (array_reverse($results) as $result) {
            $result->final = $result->type;

            if (isset($result->object)) {
                $result->final .= '{';
                $isFirst = true;

                foreach ($result->children as $pair) {
                    if (!$isFirst) {
                        $result->final .= ', ';
                    }

                    $result->final .= $pair[0] . ': ' . $pair[1]->final;
                    $isFirst = false;
                }

                $result->final .= '}';
            } elseif (isset($result->map)) {
                $result->final .= '[';
                $isFirst = true;

                foreach ($result->children as $pair) {
                    if (!$isFirst) {
                        $result->final .= ', ';
                    }

                    $result->final .=
                        $pair[0]->final . ': ' . $pair[1]->final;
                    $isFirst = false;
                }

                $result->final .= ']';
            } elseif (isset($result->sequence)) {
                $result->final .= '[';
                $isFirst = true;

                foreach ($result->children as $pair) {
                    if (!$isFirst) {
                        $result->final .= ', ';
                    }

                    $result->final .= $pair[1]->final;
                    $isFirst = false;
                }

                $result->final .= ']';
            }
        }

        foreach ($seenArrays as &$value) {
            unset($value[self::ARRAY_ID_KEY]);
        }

        return $final->final;
    }

    const ARRAY_ID_KEY = "\0__phony__\0";

    private static $instance;
    private $depth;
    private $incrementIds;
    private $objectIds;
    private $objectId;
}
