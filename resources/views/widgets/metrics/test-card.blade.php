<div {!! $attributes !!}>
    <div class="card-header d-flex justify-content-between align-items-start pb-0">
        <div>
            @if($icon)
            <div class="avatar bg-rgba-{{ $style }} p-50 m-0">
                <div class="avatar-content">
                    <i class="{{ $icon }} text-{{ $style }} font-medium-5"></i>
                </div>
            </div>
            @endif

            @if($title)
                <h4 class="card-title mb-1">{!! $title !!}</h4>
            @endif

            <div class="metric-header">{!! $header !!}</div>
        </div>

        @if (! empty($subTitle))
            <span class="btn btn-sm bg-light shadow-0 p-0">
                {{ $subTitle }}
            </span>
        @endif

        <form id="input_form" >
            <input type="text" name="search" placeholder="项目名"/>
        </form>
        <button>提交</button>
    </div>

    <div class="metric-content">{!! $content !!}</div>
</div>