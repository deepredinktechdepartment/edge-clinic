<div class="c-dashboardInfo col-lg-3 col-md-6">

    <div class="wrap">

        {{-- TITLE --}}
        <h4 class="heading heading5 hind-font medium-font-weight c-dashboardInfo__title">
            {{ $title }}
            <svg class="MuiSvgIcon-root-19" focusable="false" viewBox="0 0 24 24"
                 aria-hidden="true" role="presentation">
                <path fill="none" d="M0 0h24v24H0z"></path>
            </svg>
        </h4>

        <div class="d-flex justify-content-between text-center mt-3">

            {{-- TODAY --}}
            <a href="{{ $todayRoute ?? '#' }}" class="flex-fill border-end pe-2 text-decoration-none">
                <div class="caption-12 text-muted">Today</div>
                <span class="hind-font c-dashboardInfo__count">
                    {{ $today ?? 0 }}
                </span>
            </a>

            {{-- MONTHLY --}}
            <a href="{{ $monthRoute ?? '#' }}" class="flex-fill ps-2 text-decoration-none">
                <div class="caption-12 text-muted">Monthly</div>
                <span class="hind-font c-dashboardInfo__count">
                    {{ $month ?? 0 }}
                </span>
            </a>

        </div>

    </div>

</div>
