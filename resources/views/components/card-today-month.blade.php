<div class="c-dashboardInfo col-lg-3 col-md-6">
    <a href="{{ $route ?? '#' }}">
        <div class="wrap">

            <h4 class="heading heading5 hind-font medium-font-weight c-dashboardInfo__title">
                {{ $title }}
                <svg class="MuiSvgIcon-root-19" focusable="false" viewBox="0 0 24 24"
                     aria-hidden="true" role="presentation">
                    <path fill="none" d="M0 0h24v24H0z"></path>
                </svg>
            </h4>

            <div class="d-flex justify-content-between text-center mt-3">

                <div class="flex-fill border-end pe-2">
                    <div class="caption-12 text-muted">Today</div>
                    <span class="hind-font c-dashboardInfo__count">
                        {{ $today ?? 0 }}
                    </span>
                </div>

                <div class="flex-fill ps-2">
                    <div class="caption-12 text-muted">Monthly</div>
                    <span class="hind-font c-dashboardInfo__count">
                        {{ $month ?? 0 }}
                    </span>
                </div>

            </div>

        </div>
    </a>
</div>
