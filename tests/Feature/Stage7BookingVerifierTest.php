<?php

namespace Tests\Feature;

use App\Services\Feedback\ReviewBookingVerifier;
use Tests\TestCase;

/** Empty/fabricated references must NEVER be treated as proof of a completed order. */
class Stage7BookingVerifierTest extends TestCase
{
    public function test_untrusted_reference_alone_is_not_proof_of_a_booking(): void
    {
        $verifier = new ReviewBookingVerifier();
        $this->assertFalse($verifier->isEligible(100001, 200001, 'JW-DEMO-LOCALSTORAGE'));
        $this->assertFalse($verifier->isEligible(100002, 200002, 'JW-FAKE-STAGE7-001'));
    }
}
