<x-walk-in-layout>
    @if (session('feedback_submitted'))
        <div class="walk-in-card p-8 md:p-10 text-center">
            <p class="text-lg md:text-xl font-bold text-gray-900">Thank you</p>
            <p class="text-base text-gray-600 mt-2 max-w-sm mx-auto leading-relaxed">Your feedback has been recorded. We appreciate your time.</p>
            <a href="{{ route('walk-in.feedback.create') }}"
               class="mt-8 inline-flex items-center justify-center min-h-[2.875rem] px-8 py-[0.625rem] rounded-lg text-white text-base font-bold touch-manipulation walk-in-btn-primary">
                Start another response
            </a>
        </div>
    @else
        @php
            $startOnStepTwo = $errors->hasAny(['recommend_score', 'rating_reason', 'satisfaction_level', 'improvement_suggestions']);
        @endphp

        <div x-data="walkInFeedbackForm()" x-cloak class="walk-in-steps relative">
            {{-- Welcome popup (shown first) --}}
            <div x-show="showWelcome"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/70"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="welcome-title">
                <div class="walk-in-card w-full max-w-lg md:max-w-xl p-6 sm:p-8 md:p-10 max-h-[92vh] overflow-y-auto text-center">
                    <h2 id="welcome-title" class="text-xl md:text-2xl font-bold text-gray-900">
                        Dear Esteemed Customer / Client,
                    </h2>
                    <div class="walk-in-welcome mt-4 space-y-3 text-left md:text-center">
                        <p>Your feedback is very important to us, and we would appreciate it if you spared a minute of your time to let us know what you think about our customer service.</p>
                        <p>Thanking you in advance,</p>
                        <p class="font-semibold text-gray-900">PPMZ Customer Relations Team.</p>
                    </div>
                    <p class="mt-5 text-sm md:text-base text-gray-500">
                        We only save your name and email if you choose to provide them.
                    </p>
                    <button type="button"
                            @click="showWelcome = false"
                            class="mt-8 w-full min-h-[3.125rem] py-[0.625rem] rounded-lg text-white text-base font-bold touch-manipulation walk-in-btn-primary">
                        Continue to feedback form
                    </button>
                </div>
            </div>

            <p x-show="!showWelcome"
               class="text-center step-pill step-pill--active mb-1">
                <span x-text="step === 1 ? 'Step 1 of 2 — Your details' : 'Step 2 of 2 — Your feedback'"></span>
            </p>

            <div x-show="!showWelcome"
                 class="walk-in-card flex flex-col"
                 :class="step === 2
                    ? 'walk-in-card--step2 px-4 pt-4 pb-3 sm:px-6 sm:pt-6 sm:pb-4 md:px-7 md:pt-7 md:pb-4'
                    : 'walk-in-card--step1 px-4 pt-4 pb-3 sm:px-6 sm:pt-6 sm:pb-4 md:px-7 md:pt-7 md:pb-4'">
                <form method="POST" action="{{ route('walk-in.feedback.store') }}" class="walk-in-form flex flex-col"
                      @submit.prevent="submitForm">
                    @csrf

                    @if ($errors->any())
                        <p class="mb-3 text-sm text-red-700 bg-red-50 rounded-lg px-4 py-3">Please check the fields below and try again.</p>
                    @endif

                    {{-- Page 1 --}}
                    <div x-show="step === 1" x-transition class="walk-in-step1-panel flex flex-col gap-1 sm:gap-2">
                        <p class="text-sm md:text-base text-gray-500 leading-snug">Name and email are optional — leave blank to stay anonymous.</p>

                        <div class="flex flex-col gap-3">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <x-input-label for="client_name" value="Name (optional)" class="text-base text-gray-700 font-normal" />
                                    <x-text-input id="client_name" name="client_name" class="walk-in-field mt-1 block w-full text-lg py-3 min-h-[3.25rem]" :value="old('client_name')" autocomplete="name" />
                                    <x-input-error :messages="$errors->get('client_name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="client_email" value="Email (optional)" class="text-base text-gray-700 font-normal" />
                                    <x-text-input id="client_email" name="client_email" type="email" class="walk-in-field mt-1 block w-full text-lg py-3 min-h-[3.25rem]" :value="old('client_email')" autocomplete="email" />
                                    <x-input-error :messages="$errors->get('client_email')" class="mt-1" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="visited_at" value="Walk-in date & time" class="text-base text-gray-700 font-normal" />
                                <x-text-input id="visited_at" name="visited_at" type="datetime-local" class="walk-in-field mt-1 block w-full text-lg py-3 min-h-[3.25rem]"
                                              :value="old('visited_at', $defaultVisitedAt)" required />
                                <p class="mt-1 text-sm text-gray-500">Defaults to now — change if needed.</p>
                                <x-input-error :messages="$errors->get('visited_at')" class="mt-1" />
                            </div>
                        </div>

                        <div class="shrink-0">
                            <button type="button" @click="goToStep(2)"
                                    class="w-full min-h-[3.125rem] py-[0.625rem] rounded-lg text-white text-base font-bold touch-manipulation walk-in-btn-primary">
                                Continue
                            </button>
                        </div>
                    </div>

                    {{-- Page 2 --}}
                    <div x-show="step === 2" x-transition style="display: none;" class="walk-in-step2-panel flex flex-col gap-1 sm:gap-2">
                        <div class="shrink-0">
                            <p class="text-sm sm:text-base text-gray-800 leading-snug">
                                <span class="font-semibold text-gray-900">1.</span>
                                How likely are you to recommend PPMZ to a friend or colleague? <span class="text-gray-500">(0–10)</span>
                            </p>
                            <input type="hidden" name="recommend_score" :value="recommendScore !== null ? recommendScore : ''">
                            <div class="mt-2 grid grid-cols-11 gap-1 max-w-2xl mx-auto" role="group" aria-label="Recommendation score">
                                <template x-for="n in 11" :key="n - 1">
                                    <button type="button"
                                            @click="recommendScore = n - 1"
                                            :class="recommendScore === (n - 1) ? 'walk-in-btn-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                            class="kiosk-score-btn rounded-md font-semibold text-base md:text-lg transition touch-manipulation"
                                            x-text="n - 1"></button>
                                </template>
                            </div>
                            <p class="mt-1 text-center text-sm text-gray-500">0 = not likely · 10 = very likely</p>
                            <x-input-error :messages="$errors->get('recommend_score')" class="mt-2" />
                        </div>

                        <div class="shrink-0">
                            <x-input-label for="rating_reason" value="2. Reason for your rating" class="text-sm sm:text-base text-gray-800 font-semibold" />
                            <textarea id="rating_reason" name="rating_reason" rows="2" required
                                      class="walk-in-field mt-1.5 block w-full text-lg py-2 min-h-[3.25rem] max-h-28 resize-y"
                                      placeholder="Your answer">{{ old('rating_reason') }}</textarea>
                            <x-input-error :messages="$errors->get('rating_reason')" class="mt-1" />
                        </div>

                        <fieldset class="border-0 p-0 m-0 shrink-0 walk-in-step2-satisfaction">
                            <legend class="text-sm sm:text-base font-semibold text-gray-800 mb-1">3. Overall satisfaction</legend>
                            <div class="walk-in-satisfaction-grid walk-in-satisfaction-grid--compact mt-1 grid grid-cols-5 gap-1 overflow-visible" role="radiogroup" aria-label="Overall satisfaction">
                                @foreach ($satisfactionOptions as $value => $label)
                                    <label class="relative flex flex-col items-center justify-center gap-1 px-1 py-2 sm:px-1.5 sm:py-2 rounded-md border border-gray-200 hover:border-gray-300 cursor-pointer touch-manipulation text-center has-[:checked]:border-[var(--brand-red)] has-[:checked]:bg-red-50/40">
                                        <div x-show="satisfaction === {{ $value }}"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             class="walk-in-satisfaction-emoji is-popping"
                                             aria-hidden="true">
                                            <x-animated-satisfaction-emoji :level="$value" />
                                        </div>
                                        <input type="radio" name="satisfaction_level" value="{{ $value }}" x-model.number="satisfaction" required
                                               class="h-4 w-4 text-[var(--brand-red)] focus:ring-[var(--brand-red)]">
                                        <span class="text-gray-800 text-xs sm:text-sm leading-tight">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('satisfaction_level')" class="mt-1" />
                        </fieldset>

                        <div class="shrink-0">
                            <x-input-label for="improvement_suggestions" value="4. How can we improve? (optional)" class="text-sm sm:text-base text-gray-800 font-semibold" />
                            <textarea id="improvement_suggestions" name="improvement_suggestions" rows="2"
                                      class="walk-in-field mt-1.5 block w-full text-lg py-2 min-h-[3.25rem] max-h-28 resize-y"
                                      placeholder="Share any suggestions">{{ old('improvement_suggestions') }}</textarea>
                            <x-input-error :messages="$errors->get('improvement_suggestions')" class="mt-1" />
                        </div>

                        <div class="shrink-0 grid gap-2 grid-cols-2">
                            <button type="button" @click="goToStep(1)"
                                    class="w-full min-h-[2.875rem] py-2 rounded-lg text-base font-bold touch-manipulation walk-in-btn-ghost">
                                Back
                            </button>
                            <button type="submit"
                                    class="w-full min-h-[2.875rem] py-2 rounded-lg text-white text-base font-bold touch-manipulation walk-in-btn-primary">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function walkInFeedbackForm() {
                return {
                    showWelcome: {{ ($errors->any() || $startOnStepTwo) ? 'false' : 'true' }},
                    step: {{ $startOnStepTwo ? 2 : 1 }},
                    recommendScore: {{ old('recommend_score') !== null ? (int) old('recommend_score') : 'null' }},
                    satisfaction: {{ old('satisfaction_level') !== null ? (int) old('satisfaction_level') : 'null' }},

                    init() {
                        this.$watch('step', () => this.syncChrome());
                        this.$watch('showWelcome', () => this.syncChrome());
                        this.syncChrome();
                    },

                    syncChrome() {
                        const hideHeader = this.step === 2 && ! this.showWelcome;
                        document.getElementById('walk-in-header')?.classList.toggle('hidden', hideHeader);
                        document.documentElement.classList.toggle('walk-in-step-2', hideHeader);
                    },

                    goToStep(next) {
                        if (next === 2) {
                            const visited = document.getElementById('visited_at');
                            if (! visited?.value || ! visited.checkValidity()) {
                                visited?.reportValidity?.();
                                visited?.focus();
                                return;
                            }
                        }
                        this.step = next;
                        this.syncChrome();
                        window.scrollTo({ top: 0, behavior: 'auto' });
                    },

                    submitForm(event) {
                        const form = event.target;
                        if (this.recommendScore === null) {
                            alert('Please tap a score from 0 to 10.');
                            return;
                        }
                        if (! form.checkValidity()) {
                            form.reportValidity();
                            return;
                        }
                        form.submit();
                    },
                };
            }
        </script>
    @endif
</x-walk-in-layout>