<div x-data="{
    openPopup: false,
    calc() {
        const val = ({{ $model ?? 'cont.container_no' }}) || '';
        return window.iso6346Validator ? window.iso6346Validator.validate(val) : { status: 'empty' };
    }
}" 
class="container-validate-wrapper" 
style="position: relative; width: 100%; display: flex; align-items: center;"
@click.away="openPopup = false">

    <!-- Container Input -->
    <input type="text" 
           :name="{{ $name ?? "':name'" }}" 
           class="form-control-gf" 
           x-model="{{ $model ?? 'cont.container_no' }}"
           @input="if({{ $model ?? 'cont.container_no' }}) { {{ $model ?? 'cont.container_no' }} = {{ $model ?? 'cont.container_no' }}.toUpperCase().replace(/\s+/g, ''); }"
           :style="calc().status === 'valid' ? 'border-color: #20a464; background: #f8fffb; padding-right: 24px;' : (calc().status === 'mismatch' || calc().status === 'invalid' ? 'border-color: #dc3545; background: #fffafa; padding-right: 24px;' : '')"
           placeholder="{{ $placeholder ?? 'Container No.' }}"
           maxlength="11"
           autocomplete="off"
           spellcheck="false"
           {{ $attributes ?? '' }}>

    <!-- Status Button (Icon) inside the field -->
    <button type="button" 
            x-show="calc().status !== 'empty'"
            @click.stop="openPopup = !openPopup" 
            :title="calc().status === 'valid' ? 'Container Verified (Click details)' : 'Container Number Issue (Click to see correction)'"
            :style="calc().status === 'valid' ? 'position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; border-radius: 50%; background: #19a463; color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; z-index: 5;' : 'position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; border-radius: 50%; background: #dc3545; color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 10px; z-index: 5; box-shadow: 0 1px 4px rgba(220,53,69,0.3);'">
        <span x-text="calc().status === 'valid' ? '✓' : '⚠️'"></span>
    </button>

    <!-- Validation Details Popover -->
    <div x-show="openPopup" 
         x-cloak
         style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 280px; max-width: 320px; background: #ffffff; border: 1px solid #e4e8ef; border-radius: 8px; box-shadow: 0 10px 25px rgba(24,39,75,0.18); z-index: 99999; padding: 12px; font-size: 11px; line-height: 1.4; text-align: left;"
         @click.stop>
        
        <!-- Header -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f0f2f5;">
            <div :style="calc().status === 'valid' ? 'width: 24px; height: 24px; border-radius: 6px; background: #e8f8ef; color: #19a463; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;' : 'width: 24px; height: 24px; border-radius: 6px; background: #fff0f0; color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;'">
                <span x-text="calc().status === 'valid' ? '✓' : '⚠️'"></span>
            </div>
            <div>
                <div style="font-weight: 700; color: #1e293b; font-size: 11px;" x-text="calc().status === 'valid' ? 'Container Number Verified' : (calc().status === 'mismatch' ? 'Check Digit Mismatch' : 'Invalid Container Format')"></div>
                <div style="font-size: 9px; color: #64748b;">ISO 6346 Standard Rule</div>
            </div>
            <button type="button" @click.stop="openPopup = false" style="margin-left: auto; background: none; border: none; font-size: 14px; color: #94a3b8; cursor: pointer; padding: 0 4px;">&times;</button>
        </div>

        <!-- Issue / Reason Explanation -->
        <div style="margin-bottom: 8px; color: #475569; font-size: 10px;" x-text="calc().reason"></div>

        <!-- Digits Grid -->
        <template x-if="calc().status === 'mismatch' || calc().status === 'valid'">
            <div style="background: #f8fafc; border-radius: 4px; padding: 6px 8px; margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="color: #64748b;">Entered Check Digit:</span>
                    <span style="font-weight: 700; color: #0f172a;" x-text="calc().enteredDigit"></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Expected Check Digit:</span>
                    <span style="font-weight: 700; color: #16a34a;" x-text="calc().expectedDigit"></span>
                </div>
            </div>
        </template>

        <!-- Action Box for Corrections -->
        <template x-if="calc().action">
            <div style="background: #fff5f5; border: 1px solid #ffd4d4; border-radius: 6px; padding: 8px;">
                <div style="font-size: 9px; font-weight: 700; color: #a33a3a; margin-bottom: 2px; text-transform: uppercase;">Required Action</div>
                <div style="font-size: 11px; font-weight: 700; color: #dc2626;" x-text="calc().action"></div>
                <template x-if="calc().suggestedValue">
                    <button type="button" 
                            @click="{{ $model ?? 'cont.container_no' }} = calc().suggestedValue; openPopup = false;"
                            style="margin-top: 6px; background: #dc2626; color: #fff; border: none; padding: 3px 8px; border-radius: 3px; font-size: 10px; cursor: pointer; font-weight: 600;">
                        Auto-fix to <span x-text="calc().suggestedValue"></span>
                    </button>
                </template>
            </div>
        </template>
    </div>
</div>

