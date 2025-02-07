<li>
    <!-- Check if the value is an object or array -->
    @if (is_array($value) || is_object($value))
        <!-- Parent Node -->
        <span class="tree-toggle" style="cursor: pointer;">
            <strong>{{ $key }}:</strong>
        </span>
        <ul style="display: none;">
            @foreach ($value as $subKey => $subValue)
                @include('partials.tree-item', ['key' => $subKey, 'subKey' => $key, 'value' => $subValue])
            @endforeach
        </ul>
    @else
        <!-- Leaf Node -->
        <span>
            <strong>{{ $key }}:</strong> 
            @if (isset($value->_value))
                {{ $value->_value }}
            @else
                {{ $value }}
            @endif
        </span>

        <!-- Buttons for Set and Get -->
        @if (isset($value->writeable) && $value->writeable)
            <button class="btn btn-sm btn-primary set-button" data-key="{{ $key }}" data-parent="{{ $subKey ?? '' }}">Set</button>
        @endif
        <button class="btn btn-sm btn-secondary get-button" data-key="{{ $key }}" data-parent="{{ $subKey ?? '' }}">Get</button>
    @endif
</li>