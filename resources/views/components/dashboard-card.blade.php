<div class="c-dashboardInfo col-lg-3 col-md-6">
    <a href="{{ $route }}">
        <div class="wrap">
            <h4 class="heading heading5 hind-font medium-font-weight c-dashboardInfo__title">
                {{ $title }}
                <svg class="MuiSvgIcon-root-19" focusable="false" viewBox="0 0 24 24"
                     aria-hidden="true" role="presentation">
                    <path fill="none" d="M0 0h24v24H0z"></path>
                </svg>
            </h4>
            <span class="hind-font caption-12 c-dashboardInfo__count">{{ $count ?? 0 }}</span>
        </div>
    </a>
</div>
