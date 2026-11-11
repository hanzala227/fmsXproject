@props(['name', 'options' => [], 'valueField' => 'id', 'labelField' => 'name', 'xModel' => '', 'module' => '', 'class' => 'form-control-gf', 'required' => false, 'placeholder' => 'Select...', 'type' => ''])

<div style="flex: 1; min-width: 0; width: 100%; display: flex; align-items: center;" x-data="{
    openModal() {
        @if($module === 'trade-partner')
            window.open('/trade-partner/create', '_blank');
        @else
            window.dispatchEvent(new CustomEvent('open-add-new-modal', {
                detail: { module: '{{ $module }}', targetModel: '{{ $xModel }}', targetSelect: '{{ $name }}' }
            }));
        @endif
    }
}">
    <select 
        name="{{ $name }}" 
        class="{{ $class }}"
        {{ $required ? 'required' : '' }}
        @if($xModel) x-model="{{ $xModel }}" @endif
        @if($module && $xModel) @change="if($event.target.value === 'ADD_NEW') { openModal(); $el.value = ''; {{ $xModel }} = ''; }" @endif
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>
        
        {{-- Iterate over options, handling both collections/objects and simple arrays --}}
        @foreach($options as $key => $option)
            @php
                // Filter logic for trade-partners
                if ($module === 'trade-partner' && !empty($type)) {
                    $tpType = is_object($option) ? $option->type : (is_array($option) ? data_get($option, 'type') : '');
                    
                    // Maps or groups of types allowed
                    $allowedTypes = [];
                    if ($type === 'customer') {
                        $allowedTypes = ['CS', 'CLIENT'];
                    } elseif ($type === 'agent') {
                        $allowedTypes = ['PR', 'AGENT', 'FR'];
                    } elseif ($type === 'carrier') {
                        $allowedTypes = ['CR', 'CARRIER', 'AC'];
                    } elseif ($type === 'shipper' || $type === 'consignee' || $type === 'notify') {
                        $allowedTypes = ['CS', 'CLIENT', 'SH', 'KS', 'CN'];
                    } elseif ($type === 'cfs' || $type === 'location') {
                        $allowedTypes = ['CF', 'CY', 'WH', 'WAREHOUSE', 'CFS', 'CY'];
                    } else {
                        // exact match fallback
                        $allowedTypes = [$type];
                    }
                    
                    if (!in_array($tpType, $allowedTypes)) {
                        continue;
                    }
                }
                
                $val = is_object($option) || is_array($option) ? data_get($option, $valueField) : $key;
                $label = is_object($option) || is_array($option) ? (data_get($option, $labelField) ?? '') : $option;
                if ($module === 'port' && is_object($option) && !empty($option->code)) {
                    $label = "[{$option->code}] {$label}";
                }
            @endphp
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
        
        {{-- Render options dynamically pushed via Alpine (optional support) --}}
        <template x-for="dynOpt in ((window.dynamicOptions && window.dynamicOptions['{{ $module }}']) || [])" :key="dynOpt.id">
            <option :value="dynOpt.id" x-text="dynOpt.name"></option>
        </template>
        
        @if($module)
            <option value="ADD_NEW" style="font-weight: bold; color: #3b82f6; background-color: #f0f9ff;">+ Add New {{ str_replace('-', ' ', Str::title($module)) }}</option>
        @endif
    </select>
</div>
