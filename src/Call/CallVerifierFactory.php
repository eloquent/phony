<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;

/**
 * Creates call verifiers.
 */
class CallVerifierFactory
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
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                GeneratorVerifierFactory::instance(),
                IterableVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call verifier factory.
     *
     * @param MatcherFactory           $matcherFactory           The matcher factory to use.
     * @param MatcherVerifier          $matcherVerifier          The macther verifier to use.
     * @param GeneratorVerifierFactory $generatorVerifierFactory The generator verifier factory to use.
     * @param IterableVerifierFactory  $iterableVerifierFactory  The iterable verifier factory to use.
     * @param AssertionRecorder        $assertionRecorder        The assertion recorder to use.
     * @param AssertionRenderer        $assertionRenderer        The assertion renderer to use.
     */
    public function __construct(
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        IterableVerifierFactory $iterableVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->iterableVerifierFactory = $iterableVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
    }

    /**
     * Wrap the supplied call in a verifier.
     *
     * @param Call $call The call.
     *
     * @return CallVerifier The call verifier.
     */
    public function fromCall(Call $call): CallVerifier
    {
        return new CallVerifier(
            $call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
    }

    /**
     * Wrap the supplied calls in verifiers.
     *
     * @param array<int,Call> $calls The calls.
     *
     * @return array<int,CallVerifier> The call verifiers.
     */
    public function fromCalls(array $calls): array
    {
        $verifiers = [];

        foreach ($calls as $call) {
            $verifiers[] = new CallVerifier(
                $call,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->generatorVerifierFactory,
                $this->iterableVerifierFactory,
                $this->assertionRecorder,
                $this->assertionRenderer
            );
        }

        return $verifiers;
    }

    /**
     * @var ?self
     */
    private static $instance;

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
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;
}
