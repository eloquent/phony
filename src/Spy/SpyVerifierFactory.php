<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Hook\FunctionHookManager;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use InvalidArgumentException;

/**
 * Creates spy verifiers.
 */
class SpyVerifierFactory
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
                SpyFactory::instance(),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                GeneratorVerifierFactory::instance(),
                IterableVerifierFactory::instance(),
                CallVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                FunctionHookManager::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new spy verifier factory.
     *
     * @param SpyFactory               $spyFactory               The spy factory to use.
     * @param MatcherFactory           $matcherFactory           The matcher factory to use.
     * @param MatcherVerifier          $matcherVerifier          The macther verifier to use.
     * @param GeneratorVerifierFactory $generatorVerifierFactory The generator verifier factory to use.
     * @param IterableVerifierFactory  $iterableVerifierFactory  The iterable verifier factory to use.
     * @param CallVerifierFactory      $callVerifierFactory      The call verifier factory to use.
     * @param AssertionRecorder        $assertionRecorder        The assertion recorder to use.
     * @param AssertionRenderer        $assertionRenderer        The assertion renderer to use.
     * @param FunctionHookManager      $functionHookManager      The function hook manager to use.
     */
    public function __construct(
        SpyFactory $spyFactory,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        FunctionHookManager $functionHookManager
    ) {
        $this->spyFactory = $spyFactory;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->iterableVerifierFactory = $iterableVerifierFactory;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->functionHookManager = $functionHookManager;
    }

    /**
     * Create a new spy verifier.
     *
     * @param ?Spy $spy The spy, or null to create an anonymous spy.
     *
     * @return SpyVerifier The newly created spy verifier.
     */
    public function create(?Spy $spy): SpyVerifier
    {
        if (!$spy) {
            $spy = $this->spyFactory->create(null);
        }

        return new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
    }

    /**
     * Create a new spy verifier for the supplied callback.
     *
     * @param ?callable $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifier The newly created spy verifier.
     */
    public function createFromCallback(?callable $callback): SpyVerifier
    {
        return new SpyVerifier(
            $this->spyFactory->create($callback),
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
    }

    /**
     * Create a new spy verifier for a global function and declare it in the
     * specified namespace.
     *
     * @param callable&string $function  The function name.
     * @param string          $namespace The namespace.
     *
     * @return SpyVerifier              The newly created spy verifier.
     * @throws InvalidArgumentException If an invalid function name or namespace is specified.
     */
    public function createGlobal(
        string $function,
        string $namespace
    ): SpyVerifier {
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

        $spy = $this->spyFactory->create($function);
        $this->functionHookManager->defineFunction($function, $namespace, $spy);

        return new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
    }

    /**
     * @var ?self
     */
    private static $instance;

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
     * @var FunctionHookManager
     */
    private $functionHookManager;
}
