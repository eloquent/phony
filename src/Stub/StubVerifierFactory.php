<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyFactory;
use Eloquent\Phony\Stub\Answer\Builder\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use InvalidArgumentException;

/**
 * Creates stub verifiers.
 */
class StubVerifierFactory
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
                StubFactory::instance(),
                SpyFactory::instance(),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                GeneratorVerifierFactory::instance(),
                IterableVerifierFactory::instance(),
                CallVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                GeneratorAnswerBuilderFactory::instance(),
                FunctionHookManager::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new stub verifier factory.
     *
     * @param StubFactory                   $stubFactory                   The stub factory to use.
     * @param SpyFactory                    $spyFactory                    The spy factory to use.
     * @param MatcherFactory                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifier               $matcherVerifier               The macther verifier to use.
     * @param GeneratorVerifierFactory      $generatorVerifierFactory      The generator verifier factory to use.
     * @param IterableVerifierFactory       $iterableVerifierFactory       The iterable verifier factory to use.
     * @param CallVerifierFactory           $callVerifierFactory           The call verifier factory to use.
     * @param AssertionRecorder             $assertionRecorder             The assertion recorder to use.
     * @param AssertionRenderer             $assertionRenderer             The assertion renderer to use.
     * @param GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory The generator answer builder factory to use.
     * @param FunctionHookManager           $functionHookManager           The function hook manager to use.
     */
    public function __construct(
        StubFactory $stubFactory,
        SpyFactory $spyFactory,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory,
        FunctionHookManager $functionHookManager
    ) {
        $this->stubFactory = $stubFactory;
        $this->spyFactory = $spyFactory;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->iterableVerifierFactory = $iterableVerifierFactory;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
        $this->functionHookManager = $functionHookManager;
    }

    /**
     * Create a new stub verifier.
     *
     * If the "self" value is omitted, it will be set to the verifier itself.
     *
     * @param ?Stub $stub The stub, or null to create an anonymous stub.
     * @param mixed $self The "self" value.
     *
     * @return StubVerifier The newly created stub verifier.
     */
    public function create(?Stub $stub, $self = null): StubVerifier
    {
        if (!$stub) {
            $stub = $this->stubFactory->create(null, null);
        }

        $verifier = new StubVerifier(
            $stub,
            $this->spyFactory->create($stub),
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );

        if (func_num_args() > 1) {
            $verifier->setSelf($self);
        } else {
            $verifier->setSelf($verifier);
        }

        return $verifier;
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param ?callable $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifier The newly created stub verifier.
     */
    public function createFromCallback(?callable $callback): StubVerifier
    {
        $stub = $this->stubFactory->create($callback, null);

        $verifier = new StubVerifier(
            $stub,
            $this->spyFactory->create($stub),
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );
        $verifier->setSelf($verifier);

        return $verifier;
    }

    /**
     * Create a new stub verifier for a global function and declare it in the
     * specified namespace.
     *
     * @param callable&string $function  The function name.
     * @param string          $namespace The namespace.
     *
     * @return StubVerifier             The newly created stub verifier.
     * @throws InvalidArgumentException If an invalid function name or namespace is specified.
     */
    public function createGlobal(
        string $function,
        string $namespace
    ): StubVerifier {
        if (false !== strpos($function, '\\')) {
            throw new InvalidArgumentException(
                'Only functions in the global namespace are supported.'
            );
        }

        $namespace = trim($namespace, '\\');

        if (!$namespace) {
            throw new InvalidArgumentException(
                'The supplied namespace must not be empty.'
            );
        }

        $stub = $this->stubFactory->create($function, null);
        $spy = $this->spyFactory->create($stub);
        $this->functionHookManager->defineFunction($function, $namespace, $spy);

        $verifier = new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->generatorAnswerBuilderFactory
        );
        $verifier->setSelf($verifier);

        return $verifier;
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var StubFactory
     */
    private $stubFactory;

    /**
     * @var SpyFactory
     */
    private $spyFactory;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var MatcherVerifier
     */
    private $matcherVerifier;

    /**
     * @var GeneratorVerifierFactory
     */
    private $generatorVerifierFactory;

    /**
     * @var IterableVerifierFactory
     */
    private $iterableVerifierFactory;

    /**
     * @var CallVerifierFactory
     */
    private $callVerifierFactory;

    /**
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;

    /**
     * @var GeneratorAnswerBuilderFactory
     */
    private $generatorAnswerBuilderFactory;

    /**
     * @var FunctionHookManager
     */
    private $functionHookManager;
}
