<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Spy\Spy;

/**
 * Creates generator verifiers.
 */
class GeneratorVerifierFactory
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
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new event order verifier factory.
     *
     * @param MatcherFactory    $matcherFactory    The matcher factory to use.
     * @param AssertionRecorder $assertionRecorder The assertion recorder to use.
     * @param AssertionRenderer $assertionRenderer The assertion renderer to use.
     */
    public function __construct(
        MatcherFactory $matcherFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
    }

    /**
     * Set the call verifier factory.
     *
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     */
    public function setCallVerifierFactory(
        CallVerifierFactory $callVerifierFactory
    ): void {
        $this->callVerifierFactory = $callVerifierFactory;
    }

    /**
     * Create a new generator verifier.
     *
     * @param Spy|Call        $subject The subject.
     * @param array<int,Call> $calls   The calls.
     *
     * @return GeneratorVerifier The newly created generator verifier.
     */
    public function create($subject, array $calls): GeneratorVerifier
    {
        return new GeneratorVerifier(
            $subject,
            $calls,
            $this->matcherFactory,
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
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var AssertionRecorder
     */
    private $assertionRecorder;

    /**
     * @var AssertionRenderer
     */
    private $assertionRenderer;

    /**
     * @var CallVerifierFactory
     */
    private $callVerifierFactory;
}
