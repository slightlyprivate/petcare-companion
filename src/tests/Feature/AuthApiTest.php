<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test authentication endpoints including OTP request, verification, and user info
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_request_otp_with_valid_email(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/auth/request', [
            'email' => $email,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => __('auth.otp.success')]);

        // Verify OTP was created
        $this->assertDatabaseHas('otps', [
            'email' => $email,
        ]);

        $otp = Otp::where('email', $email)->first();
        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp->code));
        $this->assertGreaterThan(now(), $otp->expires_at);
    }

    #[Test]
    public function it_validates_email_when_requesting_otp(): void
    {
        $response = $this->postJson('/api/auth/request', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_requires_email_when_requesting_otp(): void
    {
        $response = $this->postJson('/api/auth/request', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_verify_otp_with_valid_credentials(): void
    {
        $email = 'test@example.com';
        $code = '123456';

        // Create a valid OTP
        Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/auth/verify', [
            'email' => $email,
            'code' => $code,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'email',
                ],
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        // Verify token was created
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function it_fails_verification_with_invalid_otp(): void
    {
        $this->postJson('/api/auth/request', ['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/verify', [
            'email' => 'test@example.com',
            'code' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid or expired code.',
            ]);
    }

    #[Test]
    public function it_fails_to_verify_expired_otp(): void
    {
        $email = 'test@example.com';
        $code = '123456';

        // Create an expired OTP
        Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->postJson('/api/auth/verify', [
            'email' => $email,
            'code' => $code,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired code.']);
    }

    #[Test]
    public function it_requires_email_when_verifying_otp(): void
    {
        $response = $this->postJson('/api/auth/verify', [
            'code' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_requires_code_when_verifying_otp(): void
    {
        $response = $this->postJson('/api/auth/verify', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function it_validates_email_format_when_verifying_otp(): void
    {
        $response = $this->postJson('/api/auth/verify', [
            'email' => 'invalid-email',
            'code' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_get_authenticated_user_info(): void
    {
        $user = User::create(['email' => 'test@example.com']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    #[Test]
    public function it_requires_authentication_for_user_info(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_reuses_existing_valid_otp_for_same_email(): void
    {
        $email = 'test@example.com';

        // First request
        $this->postJson('/api/auth/request', ['email' => $email]);
        $firstOtp = Otp::where('email', $email)->first();

        // Second request (should reuse existing valid OTP)
        $this->postJson('/api/auth/request', ['email' => $email]);

        $otps = Otp::where('email', $email)->get();
        $this->assertCount(1, $otps); // Should still be only one OTP
        $this->assertEquals($firstOtp->code, $otps->first()->code);
    }

    #[Test]
    public function it_creates_new_otp_when_existing_is_expired(): void
    {
        $email = 'test@example.com';

        // Create expired OTP
        Otp::create([
            'email' => $email,
            'code' => '123456',
            'expires_at' => now()->subMinutes(1),
        ]);

        // Request new OTP
        $this->postJson('/api/auth/request', ['email' => $email]);

        $otps = Otp::where('email', $email)->get();
        $this->assertCount(2, $otps); // Should have old expired and new one
    }

    #[Test]
    public function it_creates_user_on_first_authentication(): void
    {
        $email = 'newuser@example.com';
        $code = '123456';

        // Create OTP
        Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Verify OTP
        $this->postJson('/api/auth/verify', [
            'email' => $email,
            'code' => $code,
        ]);

        // Check user was created
        $this->assertDatabaseHas('users', ['email' => $email]);
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
    }

    #[Test]
    public function it_reuses_existing_user_on_authentication(): void
    {
        $email = 'existing@example.com';
        $code = '123456';

        // Create user first
        $existingUser = User::create(['email' => $email]);

        // Create OTP
        Otp::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Verify OTP
        $this->postJson('/api/auth/verify', [
            'email' => $email,
            'code' => $code,
        ]);

        // Check only one user exists
        $this->assertEquals(1, User::where('email', $email)->count());
        $user = User::where('email', $email)->first();
        $this->assertEquals($existingUser->id, $user->id);
    }
}
