
@if(count($subCategories) > 0)
<div class="courses-widget mb-4 pb-1">
    <h4 class="widget-title">{{ __('Sub Categories') }}</h4>
    <div class="courses-cat-list">
        <ul class="list-wrap">
            @foreach ($subCategories->sortBy('translation.name') as $category)
            <li>
                <div class="form-check">
                    <!-- এখানে onchange বা সাবমিট ট্রিগার করার জন্য কোড যোগ করতে হবে -->
                    <input @checked(in_array($category->id, $categoriesIds)) class="form-check-input category-checkbox" type="checkbox" value="{{ $category->id }}" id="cat_{{ $category->id }}" onchange="this.form.submit()">
                    <label class="form-check-label" for="cat_{{ $category->id }}">{{ $category->translation->name }}</label>
                </div>
            </li>
            @endforeach
        </ul>
        <div class="show-more">
        </div>
    </div>
</div>
@endif