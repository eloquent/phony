<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Exporter;

use Exception;
use ReflectionObject;
use SplObjectStorage;

/**
 * Exports values to inline strings.
 *
 * @internal
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
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new inline exporter.
     *
     * @param integer|null $depth        The depth.
     * @param boolean|null $incrementIds True if IDs should increment. Used for testing purposes.
     */
    public function __construct($depth = null, $incrementIds = null)
    {
        if (null === $depth) {
            $depth = 1;
        }
        if (null === $incrementIds) {
            $incrementIds = true;
        }

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
                    $result->type = 'resource #' . intval($value);

                    break;

                case 'string':
                    $result->type = json_encode($value, $this->jsonFlags);

                    break;

                case 'array':
                    if (isset($value['__phony__'])) {
                        $id = $value['__phony__'];
                        $displayId = $id;
                    } else {
                        $id = $value['__phony__'] = '#' . $arrayId++;

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
                        if ('__phony__' === $key) {
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

                    $reflector = new ReflectionObject($value);
                    $values = array();

                    foreach ($reflector->getProperties() as $property) {
                        if ($property->isStatic()) {
                            continue;
                        }

                        $property->setAccessible(true);

                        $values[$property->getName()] =
                            $property->getValue($value);
                    }

                    if ($value instanceof Exception) {
                        $originalValues = $values;
                        $values = array();

                        if ('' !== $originalValues['message']) {
                            $values['message'] = $originalValues['message'];
                        }
                        if (0 !== $originalValues['code']) {
                            $values['code'] = $originalValues['code'];
                        }
                        if ($previous = $value->getPrevious()) {
                            $values['previous'] = $previous;
                        }

                        unset($originalValues['message']);
                        unset($originalValues['code']);
                        unset($originalValues['file']);
                        unset($originalValues['line']);

                        $values = array_merge($values, $originalValues);
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

                // @codeCoverageIgnoreStart
                default:
                    $result->type = '???';
                // @codeCoverageIgnoreEnd
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
            unset($value['__phony__']);
        }

        return $final->final;
    }

    private static $instance;
    private $depth;
    private $incrementIds;
    private $objectIds;
    private $objectId;
}
