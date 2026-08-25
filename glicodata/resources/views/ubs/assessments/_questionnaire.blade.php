@foreach ($assessment->questionnaireVersion->schema as $question)
    @php
        $code = $question['code'];
        $value = old("answers.$code", $assessment->answers[$code] ?? null);
    @endphp

    <fieldset class="gd-question mb-4">
        <legend class="form-label fw-semibold">
            {{ $question['label'] }}
            @if ($question['required'] ?? false)
                <span class="text-danger" aria-label="obrigatória">*</span>
            @endif
        </legend>

        @if (isset($question['help']))
            <div class="form-text mb-2">{{ $question['help'] }}</div>
        @endif

        @if (($question['type'] ?? '') === 'computed_age')
            <input class="form-control" value="{{ $assessment->patient->age }} anos" disabled>
        @elseif (($question['type'] ?? '') === 'number')
            <input
                @class([
                    'form-control',
                    'is-invalid' => $errors->has("answers.$code"),
                ])
                name="answers[{{ $code }}]"
                type="number"
                value="{{ $value }}"
                min="{{ $question['min'] ?? '' }}"
                max="{{ $question['max'] ?? '' }}"
                step="{{ $question['step'] ?? '1' }}"
                {{ ($question['required'] ?? false) ? 'required' : '' }}
            >
            @error("answers.$code")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @elseif (($question['type'] ?? '') === 'choice')
            @foreach ($question['options'] as $option)
                <div class="form-check">
                    <input
                        class="form-check-input"
                        id="{{ $code }}_{{ $loop->index }}"
                        name="answers[{{ $code }}]"
                        type="radio"
                        value="{{ $option['value'] }}"
                        @checked((string) $value === (string) $option['value'])
                        {{ ($question['required'] ?? false) ? 'required' : '' }}
                    >
                    <label class="form-check-label" for="{{ $code }}_{{ $loop->index }}">
                        {{ $option['label'] }}
                    </label>
                </div>
            @endforeach
            @error("answers.$code")
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        @endif
    </fieldset>
@endforeach
