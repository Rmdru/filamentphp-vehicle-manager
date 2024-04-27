@section('title', 'Create a new account')

<div>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900 leading-9">
            Create a new account
        </h2>

        <p class="mt-2 text-sm text-center text-gray-600 leading-5 max-w">
            Or
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                sign in to your account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
            <form wire:submit.prevent="register">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 leading-5">
                        Name
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.lazy="name" id="name" type="text" required autofocus class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('name') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                    </div>

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                        Email address
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.lazy="email" id="email" type="email" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                    </div>

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 leading-5">
                        Password
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.lazy="password" id="password" type="password" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                    </div>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 leading-5">
                        Confirm Password
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.lazy="passwordConfirmation" id="password_confirmation" type="password" required class="block w-full px-3 py-2 placeholder-gray-400 border border-gray-300 appearance-none rounded-md focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                    </div>
                </div>

                <div class="mt-6">
                    <label for="country" class="block text-sm font-medium text-gray-700 leading-5">
                        Country
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <select wire:model.lazy="country" id="country" required class="block w-full px-3 py-2 placeholder-gray-400 border border-gray-300 appearance-none rounded-md focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                            <option value="">Choose your country</option>
                            <option value="nl">Nederland (Netherlands)</option>
                            <option value="de">Deutschland (Germany)</option>
                            <option value="fr">France (France)</option>
                            <option value="it">Italia (Italy)</option>
                            <option value="be">België (Belgium)</option>
                            <option value="uk">United Kingdom (United Kingdom)</option>
                            <option value="es">España (Spain)</option>
                            <option value="pt">Portugal (Portugal)</option>
                            <option value="gr">Ελλάδα (Greece)</option>
                            <option value="se">Sverige (Sweden)</option>
                            <option value="fi">Suomi (Finland)</option>
                            <option value="dk">Danmark (Denmark)</option>
                            <option value="pl">Polska (Poland)</option>
                            <option value="cz">Česko (Czechia)</option>
                            <option value="hu">Magyarország (Hungary)</option>
                            <option value="at">Österreich (Austria)</option>
                            <option value="ch">Schweiz (Switzerland)</option>
                            <option value="no">Norge (Norway)</option>
                            <option value="ie">Éire (Ireland)</option>
                            <option value="ro">România (Romania)</option>
                            <option value="bg">България (Bulgaria)</option>
                            <option value="hr">Hrvatska (Croatia)</option>
                            <option value="sk">Slovensko (Slovakia)</option>
                            <option value="si">Slovenija (Slovenia)</option>
                            <option value="lt">Lietuva (Lithuania)</option>
                            <option value="lv">Latvija (Latvia)</option>
                            <option value="ee">Eesti (Estonia)</option>
                            <option value="lu">Luxembourg (Luxembourg)</option>
                            <option value="mt">Malta (Malta)</option>
                            <option value="cy">Κύπρος (Cyprus)</option>
                            <option value="is">Ísland (Iceland)</option>
                            <option value="li">Liechtenstein (Liechtenstein)</option>
                            <option value="mc">Monaco (Monaco)</option>
                            <option value="sm">San Marino (San Marino)</option>
                            <option value="va">Città del Vaticano (Vatican City)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="language" class="block text-sm font-medium text-gray-700 leading-5">
                        Language
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <select wire:model.lazy="language" id="language" required class="block w-full px-3 py-2 placeholder-gray-400 border border-gray-300 appearance-none rounded-md focus:outline-none focus:ring-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                            <option value="">Choose your language</option>
                            <option value="en">English (English)</option>
                            <option value="nl">Nederlands (Dutch)</option>
                            <option value="de">Deutsch (German)</option>
                            <option value="fr">Français (French)</option>
                            <option value="it">Italiano (Italian)</option>
                            <option value="es">Español (Spanish)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <span class="block w-full rounded-md shadow-sm">
                        <button type="submit" class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                            Register
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
