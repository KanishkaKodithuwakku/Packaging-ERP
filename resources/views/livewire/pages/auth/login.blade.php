<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-guest-layout>
    <div class="flex  justify-center ">
        <!-- Left Half - Form Section (50%) -->
        <div class=" bg-white w-full flex items-center justify-center " style="height: 100vh;">
            <div class="w-full max-w-md px-8">
                <!-- Logo -->
                <div class="mb-8" style="margin-bottom: 30%;">
                    <img src="{{ asset('src/images/logo/client-logo.png') }}" alt="Logo" width="200" class="mx-auto"/>
                </div>

                <!-- Form -->
                <div class="mb-5 sm:mb-8">
                    <h1 class="mb-2  text-gray-800 text-3xl font-bold dark:text-white/90 sm:text-title-md">
                        Sign In
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Enter your email and password to sign in!
                    </p>
                </div>

                <form wire:submit="login">
                    @csrf

                    <div class="mt-4 space-y-2">
                        <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                        <input wire:model="form.email" id="email" class="block w-full px-4 py-2 border rounded-md" type="email" name="email"
                            :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                    </div>

                    <div class="mt-4 space-y-2">
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                        <input wire:model="form.password" id="password" class="block w-full px-4 py-2 border rounded-md" type="password" name="password"
                            required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                    </div>

                    <div class="block mt-4">
                        <label for="remember_me" class="flex items-center">
                            <input wire:model="form.remember" id="remember_me" name="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <div class="text-left">
                            @if (Route::has('password.request'))
                                <a class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Log in') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Half - Grid Shape Section (50%) -->
        <div class=" bg-blue-800 relative w-full h-auto flex items-center justify-center" style="background-color: #161950;">
            <!-- ===== Common Grid Shape Start ===== -->
            <div class="absolute right-0 top-0 -z-1 w-[250px] xl:w-[450px]">
                <img src="{{ asset('src/images/shape/grid-01.svg') }}" alt="grid" class="w-full h-auto" />
            </div>
            <div class="absolute bottom-0 left-0 -z-1 w-full max-w-[250px] rotate-180 xl:max-w-[450px]">
                <img src="{{ asset('src/images/shape/grid-01.svg') }}" alt="grid" />
            </div>

            <div class="flex flex-col items-center max-w-xs z-10">
                <a href="#" class="block mb-4">
                    <img src="{{ asset('src/images/logo/auth-logo.png') }}" alt="Logo" />
                </a>

            </div>
        </div>
    </div>
</x-guest-layout>

