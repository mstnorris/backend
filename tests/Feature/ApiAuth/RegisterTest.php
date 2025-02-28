<?php

namespace Tests\Feature\ApiAuth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;

class RegisterTest extends ApiAuthTestCase
{
    /** @test */
    public function canRegister()
    {
        $response = $this->attemptToRegister();
        $response->assertStatus(200);
        // $this->assertAuthenticatedAs(User::first());
    }

    /** @test */
    public function cannotRegisterWithRegisteredEmailAddress()
    {
        $user = User::factory()->create([
            'email' => $this->validEmail,
            'password' => Hash::make($this->validPassword),
            'first_name' => 'John',
            'last_name' => 'Smith'
        ]);
        $this->attemptToRegisterAndExpectFail([
            'email' => $user->email,
        ], [
            'email' => Lang::get('validation.unique', ['attribute' => 'email']),
        ]);
    }


    /** @test */
    public function emailIsRequired()
    {
        $this->attemptToRegisterAndExpectFail([
            'email' => '',
        ], [
            'email' => Lang::get('validation.required', ['attribute' => 'email']),
        ]);
    }

    /** @test */
    public function emailMustBeAValidEmailAddress()
    {
        $this->attemptToRegisterAndExpectFail([
            'email' => 'invalid-email',
        ], [
            'email' => Lang::get('validation.email', ['attribute' => 'email']),
        ]);
    }

    /** @test */
   /**
    public function nameIsRequired()
    {
        $this->attemptToRegisterAndExpectFail([
            'name' => '',
        ], [
            'name' => Lang::get('validation.required', ['attribute' => 'name']),
        ]);
    }
**/

    /** @test */
    public function passwordIsRequired()
    {
        $this->attemptToRegisterAndExpectFail([
            'password' => '',
        ], [
            'password' => Lang::get('validation.required', ['attribute' => 'password']),
        ]);
    }

    /** @test */
    public function passwordMustBeAtLeast8Characters()
    {
        $this->attemptToRegisterAndExpectFail([
            'password' => '123',
            'password_confirmation' => '123',
        ], [
            'password' => Lang::get('validation.min.string', ['attribute' => 'password','min' => '8']),
        ]);
    }

    /** @test */
    public function passwordsMustMatch()
    {
        $this->attemptToRegisterAndExpectFail([
            'password' => $this->validPassword,
            'password_confirmation' => 'not-matching',
        ], [
            'password' => Lang::get('validation.confirmed', ['attribute' => 'password']),
        ]);
    }

    protected function attemptToRegister(array $params = [])
    {
        return $this->postJson($this->registerRoute, array_merge([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => $this->validEmail,
            'password' => $this->validPassword,
            'password_confirmation' => $this->validPassword,
        ], $params));
    }

    /**
     * @param array $params
     * @param string|array $errors
     */
    protected function attemptToRegisterAndExpectFail(array $params, $errors)
    {
        $response = $this->attemptToRegister($params);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($errors);

        return $response;
    }
}
