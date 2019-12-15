<?php

declare(strict_types=1);

namespace Eloquent\Phony\Exporter;

use Closure;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Mock\Handle\Handle;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandle;
use Eloquent\Phony\Mock\Method\WrappedMethod;
use Eloquent\Phony\Mock\Mock;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\IterableSpy;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubVerifier;
use Generator;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionReference;
use SplObjectStorage;
use Throwable;
use WeakReference;

/**
 * Exports values to inline strings.
 */
class InlineExporter implements Exporter
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(
                1,
                Sequencer::sequence('exporter-array-id'),
                Sequencer::sequence('exporter-object-id'),
                InvocableInspector::instance(),
                FeatureDetector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new inline exporter.
     *
     * @param int                $depth              The depth.
     * @param Sequencer          $arraySequencer     The array sequencer to use.
     * @param Sequencer          $objectSequencer    The object sequencer to use.
     * @param InvocableInspector $invocableInspector The invocable inspector to use.
     * @param FeatureDetector    $featureDetector    The feature detector to use.
     */
    public function __construct(
        int $depth,
        Sequencer $arraySequencer,
        Sequencer $objectSequencer,
        InvocableInspector $invocableInspector,
        FeatureDetector $featureDetector
    ) {
        $this->depth = $depth;
        $this->arraySequencer = $arraySequencer;
        $this->objectSequencer = $objectSequencer;
        $this->invocableInspector = $invocableInspector;
        $this->featureDetector = $featureDetector;
        $this->arrayIds = [];
        $this->objectIds = [];

        $this->isReferenceReflectionSupported =
            $featureDetector->isSupported('reflection.reference');
        $this->arrayCountOffset = $this->isReferenceReflectionSupported ? 0 : 1;
    }

    /**
     * Set the default depth.
     *
     * Negative depths are treated as infinite depth.
     *
     * @param int $depth The depth.
     *
     * @return int The previous depth.
     */
    public function setDepth(int $depth): int
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
     * @param mixed $value The value.
     * @param ?int  $depth The depth, or null to use the default.
     *
     * @return string The exported value.
     */
    public function export(&$value, int $depth = null): string
    {
        if (null === $depth) {
            $depth = $this->depth;
        }

        $final = new ExporterResult();
        $stack = [[&$value, $final, 0, gettype($value)]];
        $results = [];
        $seenWrappers = new SplObjectStorage();
        $seenObjects = new SplObjectStorage();
        $seenArrays = [];
        $arrayResults = [];
        $arrayId = 0;

        while (!empty($stack)) {
            /** @var array<int,mixed> */
            $entry = array_shift($stack);
            $value = &$entry[0];
            /** @var ExporterResult */
            $result = $entry[1];
            $currentDepth = $entry[2];
            $type = $entry[3];
            $results[] = $result;

            switch ($type) {
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
                    /** @var string */
                    $encoded = json_encode($value, self::JSON_FLAGS);
                    $result->type = $encoded;

                    break;

                case 'array':
                    if ($this->isReferenceReflectionSupported) {
                        /** @var ReflectionReference */
                        $reference =
                            ReflectionReference::fromArrayElement([&$value], 0);
                        $referenceId = $reference->getId();

                        if (isset($this->arrayIds[$referenceId])) {
                            $id = $this->arrayIds[$referenceId];
                        } else {
                            $id = $this->arrayIds[$referenceId] =
                                $this->arraySequencer->next();
                        }
                    } elseif (isset($value[self::ARRAY_ID_KEY])) {
                        $id = $value[self::ARRAY_ID_KEY];
                    } else {
                        $id = $value[self::ARRAY_ID_KEY] = $arrayId++;
                    }

                    if (!$this->isReferenceReflectionSupported) {
                        $seenArrays[$id] = &$value;
                    }

                    if (isset($arrayResults[$id])) {
                        $result->type = '&' . $id . '[]';

                        break;
                    }

                    $result->type = '#' . $id;

                    if ($depth > -1 && $currentDepth >= $depth) {
                        $count = count($value) - $this->arrayCountOffset;

                        if ($count) {
                            $result->type .= '[~' . $count . ']';
                        } else {
                            $result->type .= '[]';
                        }

                        break;
                    }

                    $arrayResults[$id] = $result;

                    $result->sequence = true;
                    $sequenceKey = 0;

                    foreach ($value as $key => &$childValue) {
                        if (
                            !$this->isReferenceReflectionSupported &&
                            self::ARRAY_ID_KEY === $key
                        ) {
                            continue;
                        }

                        if ($result->sequence) {
                            if ($key !== $sequenceKey++) {
                                $result->map = true;
                                $result->sequence = false;
                            }
                        }

                        $keyResult = new ExporterResult();
                        $valueResult = new ExporterResult();
                        $result->children[] = [$keyResult, $valueResult];

                        $stack[] = [
                            $key,
                            $keyResult,
                            $currentDepth + 1,
                            gettype($key),
                        ];
                        $stack[] = [
                            &$childValue,
                            $valueResult,
                            $currentDepth + 1,
                            gettype($childValue),
                        ];
                    }

                    break;

                case 'object':
                    $hash = spl_object_hash($value);

                    if (isset($this->objectIds[$hash])) {
                        $id = $this->objectIds[$hash];
                    } else {
                        $id = $this->objectIds[$hash] =
                            $this->objectSequencer->next();
                    }

                    if ($seenWrappers->contains($value)) {
                        $result->type = '&' . $id . '()';

                        break;
                    }

                    if ($seenObjects->contains($value)) {
                        $result->type = '&' . $id . '{}';

                        break;
                    }

                    $isClosure = false;
                    $isException = false;
                    $isGeneratorSpy = false;
                    $isHandle = false;
                    $isIterableSpy = false;
                    $isSpy = false;
                    $isSpyVerifier = false;
                    $isStaticHandle = false;
                    $isStub = false;
                    $isStubVerifier = false;
                    $isWeakReference = false;
                    $isWrapper = false;

                    if ($value instanceof Closure) {
                        $isClosure = true;
                    } elseif ($value instanceof Throwable) {
                        $isException = true;
                    } elseif ($value instanceof Generator) {
                        $isWrapper = isset($value->_phonySubject);
                        $isGeneratorSpy = $isWrapper;
                    } elseif ($value instanceof Handle) {
                        $isWrapper = true;
                        $isHandle = true;
                        $isStaticHandle = $value instanceof StaticHandle;
                    } elseif ($value instanceof Stub) {
                        $isWrapper = true;
                        $isStub = true;
                        $isStubVerifier = $value instanceof StubVerifier;
                    } elseif ($value instanceof Spy) {
                        $isWrapper = true;
                        $isSpy = true;
                        $isSpyVerifier = $value instanceof SpyVerifier;
                    } elseif ($value instanceof IterableSpy) {
                        $isWrapper = true;
                        $isIterableSpy = true;
                    } elseif ($value instanceof WeakReference) {
                        $isWrapper = true;
                        $isWeakReference = true;
                    }

                    $isMock = $value instanceof Mock;

                    if ($isClosure) {
                        $result->type = 'Closure';
                    } elseif ($isHandle) {
                        if ($isStaticHandle) {
                            $result->type = 'static-handle';
                        } else {
                            $result->type = 'handle';
                        }
                    } elseif ($isStub) {
                        $result->type = 'stub';
                    } elseif ($isSpy) {
                        $result->type = 'spy';
                    } elseif ($isGeneratorSpy) {
                        $result->type = 'generator-spy';
                    } elseif ($isIterableSpy) {
                        $result->type = 'iterable-spy';
                    } elseif ($isWeakReference) {
                        $result->type = 'weak';
                    } else {
                        $result->type = get_class($value);
                    }

                    $phpValues = (array) $value;

                    if ($isHandle) {
                        if ($isStaticHandle) {
                            $propertyName =
                                "\0" . StaticHandle::class . "\0class";
                            $result->child = new ExporterResult();
                            $result->child->final =
                                $phpValues[$propertyName]->getName();
                        } else {
                            $propertyName =
                                "\0" . InstanceHandle::class . "\0mock";
                            $result->child = new ExporterResult();
                            $stack[] = [
                                $phpValues[$propertyName],
                                $result->child,
                                $currentDepth,
                                'object',
                            ];
                        }
                    } elseif ($isSpy) {
                        if ($isSpyVerifier) {
                            $propertyName =
                                "\0" . SpyVerifier::class . "\0spy";
                            $phpValues = (array) $phpValues[$propertyName];
                        }

                        if (!$phpValues["\0*\0isAnonymous"]) {
                            $propertyName = "\0*\0callback";
                            $result->child = new ExporterResult();
                            $result->child->final = $this->exportCallable(
                                $phpValues[$propertyName]
                            );
                        }

                        $result->label = $phpValues["\0*\0label"];
                    } elseif ($isStub) {
                        if ($isStubVerifier) {
                            $phpValues = (array) $phpValues[
                                "\0" . StubVerifier::class . "\0stub"
                            ];
                        }

                        if (!$phpValues["\0*\0isAnonymous"]) {
                            $propertyName = "\0*\0callback";
                            $result->child = new ExporterResult();
                            $result->child->final = $this->exportCallable(
                                $phpValues[$propertyName]
                            );
                        }

                        $result->label = $phpValues["\0*\0label"];
                    } elseif ($isGeneratorSpy) {
                        $result->child = new ExporterResult();
                        $stack[] = [
                            $value->_phonySubject,
                            $result->child,
                            $currentDepth,
                            'object',
                        ];
                    } elseif ($isIterableSpy) {
                        $iterable = $value->iterable();
                        $result->child = new ExporterResult();
                        $stack[] = [
                            $iterable,
                            $result->child,
                            $currentDepth,
                            gettype($iterable),
                        ];
                    } elseif ($isWeakReference) {
                        $result->child = new ExporterResult();
                        $stack[] = [
                            $value->get(),
                            $result->child,
                            $currentDepth,
                            'object',
                        ];
                    }

                    if ($isWrapper) {
                        $result->wrapper = true;
                        $result->type .= '#' . $id;
                        $seenWrappers->offsetSet($value, true);
                    } else {
                        unset($phpValues["\0gcdata"]);

                        if ($isMock) {
                            $handleProperty =
                                "\0" . $result->type . "\0_handle";

                            if ($phpValues[$handleProperty]) {
                                $result->label =
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
                        } elseif ($isClosure) {
                            $reflector = new ReflectionFunction($value);
                            /** @var string */
                            $filename = $reflector->getFilename();
                            $result->label =
                                basename($filename) . ':' .
                                $reflector->getStartLine();
                            $phpValues = [];
                        }

                        $properties = [];
                        $propertyCounts = [];

                        foreach (
                            $phpValues as $propertyName => $propertyValue
                        ) {
                            if (
                                preg_match(
                                    '/^\x00([^\x00]+)\x00([^\x00]+)$/',
                                    (string) $propertyName,
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
                                    $realName =
                                        $matches[1] . '.' . $propertyName;
                                }

                                $properties[] = [
                                    $propertyName,
                                    $realName,
                                    $propertyValue,
                                ];
                            } else {
                                $properties[] = [
                                    $propertyName,
                                    $propertyName,
                                    $propertyValue,
                                ];
                            }

                            if (isset($propertyCounts[$propertyName])) {
                                $propertyCounts[$propertyName] += 1;
                            } else {
                                $propertyCounts[$propertyName] = 1;
                            }
                        }

                        $values = [];

                        foreach ($properties as $property) {
                            list($shortName, $realName, $propertyValue) =
                                $property;

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

                        $result->type .= '#' . $id;

                        if ($depth > -1 && $currentDepth >= $depth) {
                            if (empty($values)) {
                                $result->type .= '{}';
                            } else {
                                $result->type .= '{~' . count($values) . '}';
                            }

                            break;
                        }

                        $seenObjects->offsetSet($value, true);

                        $result->object = true;

                        /** @var string $key */
                        foreach ($values as $key => &$childValue) {
                            $keyResult = new ExporterResult();
                            $keyResult->final = $key;
                            $valueResult = new ExporterResult();
                            $result->children[] = [$keyResult, $valueResult];

                            $stack[] = [
                                &$childValue,
                                $valueResult,
                                $currentDepth + 1,
                                gettype($childValue),
                            ];
                        }
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

            if ($result->wrapper) {
                if ($result->child) {
                    $result->final .= '(' . $result->child->final . ')';
                }
            } elseif ($result->object) {
                $result->final .= '{';
                $isFirst = true;

                foreach ($result->children as $pair) {
                    if (!$isFirst) {
                        $result->final .= ', ';
                    }

                    $result->final .= $pair[0]->final . ': ' . $pair[1]->final;
                    $isFirst = false;
                }

                $result->final .= '}';
            } elseif ($result->map) {
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
            } elseif ($result->sequence) {
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

            if ($result->label) {
                $result->final .= '[' . $result->label . ']';
            }
        }

        if (!$this->isReferenceReflectionSupported) {
            foreach ($seenArrays as &$value) {
                unset($value[self::ARRAY_ID_KEY]);
            }
        }

        return $final->final;
    }

    /**
     * Export a string representation of a callable value.
     *
     * @param callable $callback The callable.
     *
     * @return string The exported callable.
     */
    public function exportCallable(callable $callback): string
    {
        $wrappedCallback = null;

        while ($callback instanceof WrappedInvocable) {
            $wrappedCallback = $callback;
            $innerCallback = $callback->callback();

            if (!$innerCallback) {
                break;
            }

            $callback = $innerCallback;
        }

        $label = '';

        if ($wrappedCallback) {
            if ($wrappedCallback->isAnonymous()) {
                return $this->export($wrappedCallback);
            }

            $label = $wrappedCallback->label();

            if ('' !== $label) {
                $label = '[' . $label . ']';
            }
        }

        if ($callback instanceof Closure) {
            return $this->export($callback) . $label;
        }

        $reflector = $this->invocableInspector->callbackReflector($callback);

        if (!$reflector instanceof ReflectionMethod) {
            return $reflector->getName() . $label;
        }

        $class = $reflector->getDeclaringClass();
        $name = $reflector->getName();

        if ($class->implementsInterface(Mock::class)) {
            if (
                ($parentClass = $class->getParentClass()) &&
                $parentClass->hasMethod($name)
            ) {
                $class = $parentClass;
            } else {
                try {
                    $prototype = $reflector->getPrototype();
                    $class = $prototype->getDeclaringClass();
                } catch (ReflectionException $e) {
                    // ignore
                }
            }
        }

        $atoms = explode('\\', $class->getName());
        $rendered = array_pop($atoms);

        if ($wrappedCallback instanceof WrappedMethod) {
            $name = $wrappedCallback->name();
            $handle = $wrappedCallback->handle();

            if ($handle instanceof InstanceHandle) {
                $label = $handle->label();

                if ('' !== $label) {
                    $rendered .= '[' . $label . ']';
                }
            }
        }

        if ($reflector->isStatic()) {
            $callOperator = '::';
        } else {
            $callOperator = '->';
        }

        return $rendered . $callOperator . $name;
    }

    /**
     * Reset the internal state of the exporter.
     *
     * Used for testing purposes only.
     */
    public function reset(): void
    {
        $this->arrayIds = [];
        $this->arraySequencer->reset();

        $this->objectIds = [];
        $this->objectSequencer->reset();
    }

    const ARRAY_ID_KEY = "\0__phony__\0";
    const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var Sequencer
     */
    private $arraySequencer;

    /**
     * @var Sequencer
     */
    private $objectSequencer;

    /**
     * @var InvocableInspector
     */
    private $invocableInspector;

    /**
     * @var FeatureDetector
     */
    private $featureDetector;

    /**
     * @var array<int|string,int>
     */
    private $arrayIds;

    /**
     * @var array<string,int>
     */
    private $objectIds;

    /**
     * @var bool
     */
    private $isReferenceReflectionSupported;

    /**
     * @var int
     */
    private $arrayCountOffset;
}
