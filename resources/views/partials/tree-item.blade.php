<li>
    <div class="node-content">
        <span class="expand-icon">{{ $value['is_object'] ? '▶' : '' }}</span>
        <span class="node-name">{{ $key }}</span>
        @if (!$value['is_object'] && $value['value'] !== null)
            <span class="node-value" id="{{ $value['path'] }}" style="margin-right: 10px;">{{ $value['value'] }}</span>
        @endif
        <div class="actions" style="display: flex; align-items: center;">
            <!-- Get Icon -->
            <i class="get-button fa-solid fa-rotate-right get-icon" data-path="{{ $value['path'] }}" data-type="{{ $value['type'] }}" style="cursor: pointer; margin-right: 5px;"></i>
            @if (!$value['is_object'] && $value['writable'])
            <!-- Set Icon -->
            <i class="set-button fa-solid fa-pen set-icon" data-path="{{ $value['path'] }}" data-type="{{ $value['type'] }}" data-value="{{ $value['value'] }}" style="cursor: pointer;"></i>
            @endif
        </div>
    </div>
    @if ($value['is_object'] && isset($value['children']))
        <ul class="collapsed">
            @foreach ($value['children'] as $subKey => $subValue)
                @include('partials.tree-item', ['key' => $subKey, 'value' => $subValue])
            @endforeach
        </ul>
    @endif
</li>