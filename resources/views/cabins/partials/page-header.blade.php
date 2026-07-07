<div class="cabin-pagebar">
    <div class="cabin-pagebar-main">
        @if(!empty($titleIcon))
            <div class="cabin-pagebar-icon">
                <i class="{{ $titleIcon }}"></i>
            </div>
        @endif
        <div>
            <h5 class="mb-1">{{ $title ?? ($pageTitle ?? '') }}</h5>
            @if(!empty($subtitle))
                <div class="text-muted">{{ $subtitle }}</div>
            @endif
        </div>
    </div>
    @if(!empty($actions))
        <div class="cabin-page-actions">
            @foreach($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    @if(!empty($action['target'])) target="{{ $action['target'] }}" @endif
                    class="btn {{ $action['class'] ?? 'btn-outline-secondary' }} {{ $action['size'] ?? 'btn-sm' }}">
                    @if(!empty($action['icon']))
                        <i class="{{ $action['icon'] }}"></i>
                    @endif
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
