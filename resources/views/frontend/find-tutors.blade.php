@extends('layouts.frontend-app')
@section('content')
<div class="am-find-tutors-area">
    <div class="am-searchhead">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ol class="am-breadcrumb">
                        <li><a href="{{ url('/') }}">{{ __('sidebar.home') }}</a></li>
                        <li><em>/</em></li>
                        <li class="active"><span>{{ __('sidebar.find_tutor') }}</span></li>
                    </ol>
                    <div class="am-searchhead_title">
                        <h1 class="title1">{{ __('sidebar.discover_tutor_text1') }}</h1>
                        <h1 class="title2">{{ __('sidebar.discover_tutor_text2') }}</h1>
                        <p>{{ __('sidebar.discover_tutor_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="am-searchfilter_wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="am-searchfilter_tabs">
                    
                        <div class="am-clearfilterbtn d-none">
                            <a href="javascript:void(0);" id="clear_filters">{{ __('general.clear_all_filter') }}
                                <i class="am-icon-multiply-02"></i>
                            </a>
                        </div>
                    </div>
                    <div class="am-searchfilter">
                        <div class="am-searchfilter_item">
                            <span class="am-searchfilter_title">{{ __('subject.subject_group') }}</span>
                            <span class="am-select">
                                <select id="group_id" class="am-select2" data-searchable="true"
                                    data-class="am-filter-dropdown"
                                    data-placeholder="{{ __('subject.choose_subject_group') }}">
                                    <option> </option>
                                    @foreach ($subjectGroups as $group)
                                    <option value="{{ $group->id }}" {{ $group->id == ($filters['group_id'] ?? '') ?
                                        'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                        <div class="am-searchfilter_item">
                            <span class="am-searchfilter_title">{{ __('subject.choose_subject_label') }}</span>
                            <span class="am-select">
                                <select id="subject_id" class="am-select2" multiple data-searchable="true"
                                    data-class="am-filter-dropdown"
                                    data-placeholder="{{ __('subject.choose_subject_label') }}">
                                    <option> </option>
                                    @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ in_array($subject->id, $filters['subject_id'] ??
                                        []) ? 'selected' : '' }}>{{ $subject?->name }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </div>
                     
                         <!-- <div class="am-searchfilter_item">
                            <span class="am-searchfilter_title">{{ __('general.tutor_location') }}</span>
                            <span class="am-select">
                                @if(!empty(setting('_api.google_places_api_key')))
                                <input type="text" class="form-control" id="map_location" value="{{ $filters['country'] ?? '' }}"
                                    placeholder="{{ __('general.enter_tutor_location') }}">
                                @else
                                <select class="am-select2" id="tutor_country" data-searchable="true"
                                    data-class="am-sort_dp_option am-sort-location" data-placeholder="{{ __('general.search_by_country') }}">
                                    <option> </option>
                                    @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" {{ $country->id == ($filters['country'] ?? '') ?
                                        'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @endif
                            </span>
                        </div>-->
                    </div>
                     <div class="am-searchfilteritems">
                        <div class="am-searchfilter_left">
                            <div class="am-searchinput">
                                <input type="text" value="{{ $filters['keyword'] ?? '' }}"
                                    placeholder="{{ __('general.search_by_keyword') }}" class="form-control" id="keyword">
                                <span class="am-searchinput_icon">
                                    <i class="am-icon-search-02"></i>
                                </span>
                            </div>
                            <span class="am-select">
                                <span class="am-select_title">{{ __('general.sort_by') }}:</span>
                                <select class="am-select2" id="sort_by" data-searchable="false"
                                    data-class="am-sort_dp_option" data-placeholder="{{ __('general.sort_by') }}">
                                    <option> </option>
                                    <option value="newest" {{ (($filters['sort_by'] ?? '') == 'newest' ? 'selected' : '') }}>{{ __('general.newest_first') }}</option>
                                    <option value="oldest" {{ (($filters['sort_by'] ?? '') == 'oldest' ? 'selected' : '') }}>{{ __('general.oldest_first') }}</option>
                                    <option value="asc" {{ (($filters['sort_by'] ?? '') == 'asc' ? 'selected' : '') }}>{{ __('general.sort_by_a_z') }}</option>
                                    <option value="desc" {{ (($filters['sort_by'] ?? '') == 'desc' ? 'selected' : '') }}>{{ __('general.sort_by_z_a') }}</option>
                                </select>
                            </span>
                          
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
    <div class="am-tutorsearch_section">
        <div class="container">
            <div class="row">
                <livewire:components.search-tutor :filters="$filters" wire:key="tutors-list-{{ time() }}" />
                @if(!empty(setting('_lernen.help_section_media')) ||
                !empty(setting('_lernen.help_section_title')) ||
                !empty(setting('_lernen.help_section_description')) ||
                !empty(setting('_lernen.help_section_bullets')) ||
                !empty(setting('_lernen.or_section_title')) ||
                !empty(setting('_lernen.or_section_description'))
                )
                <div class="col-12 col-lg-4 col-xl-3">
                  
                    <img src="{{ asset('images/Tugo_With_Glasses.png') }}" alt="Tugo" class="fixed-image">
                    
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@push('styles')
@vite([
'public/css/flags.css',
'public/css/videojs.css'
])
@endpush
@push('scripts')
<script src="{{ asset('js/video.min.js') }}"></script>
@if( !empty(setting('_api.google_places_api_key')))
<script async src="https://maps.googleapis.com/maps/api/js?key={{ setting('_api.google_places_api_key') }}&libraries=places&loading=async&callback=initializePlaceApi"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
                address = '';
                window.session_type = '';
                var filter_record       = @js($filters);
                var applyFilter         = true;
                let timeout;
                setTimeout(() => {
                    clearFilters();
                }, 500);

                jQuery('.am-select2').each((index, item) => {
                    let _this = jQuery(item);
                    searchable = _this.data('searchable');
                    let params = {
                        dropdownCssClass: _this.data('class'),
                        placeholder: _this.data('placeholder'),
                        allowClear: true
                    }
                    if(!searchable){
                        params['minimumResultsForSearch'] = Infinity;
                    }
                    _this.select2(params);
                });

                function initializePlaceApi() {
                    var tutorAddress = document.getElementById('map_location');

                    if(typeof google != 'undefined' && typeof google.maps.places != 'undefined'){
                        var autocompleteTutor = new google.maps.places.Autocomplete(tutorAddress);
                        google.maps.event.addListener(autocompleteTutor, 'place_changed', function () {
                            var place = autocompleteTutor.getPlace();
                            place.address_components?.forEach((item) => {
                                if(item.types.includes('country')){
                                    filter_record['country'] = item.long_name
                                }
                            });
                            applySearchFilter()
                        });
                    }
                }

                jQuery(document).on('input', '#max_price, #keyword',function (event){
                    clearTimeout(timeout);
                    filter_record[event.target.id] = event.target.value
                    timeout = setTimeout(() => applySearchFilter(), 300);
                });

                 jQuery(document).on('change', '#tutor_country',function (e){
                    filter_record['country'] = $('#tutor_country')?.select2("val");
                   applySearchFilter()
                });

                jQuery(document).on('click', '#clear_filters',function (e){
                    filter_record = {}
                    $('#keyword').val('');
                    $('#max_price').val('');
                    $('#map_location')?.val('');
                    $('#availability')?.val(null).trigger('change');
                    $('#group_id')?.val(null).trigger('change');
                    $('#subject_id')?.val(null).trigger('change');
                    $('#tutor_country')?.val(null)?.trigger('change');
                    $('#language_id')?.val(null)?.trigger('change');
                    $('#clear_filters').parent().addClass('d-none');
                    applySearchFilter(false);
                    let newUrl = `${window.location.pathname}`;
                    window.history.replaceState({}, '', newUrl);
                });

                jQuery(document).on('click', '.am-session-tab',function (e){
                    let _this = jQuery(this);
                    jQuery('.am-session-tab').removeClass('active');
                    _this.addClass('active');
                    filter_record['session_type'] = _this.data('type');
                    applySearchFilter(false);
                });

                jQuery(document).on('change', '#group_id, #availability, #sort_by, #per_page', function (e){
                    let value = $('#'+e.target.id).select2("val");
                    filter_record[e.target.id] = value?.length > 0 ? value : null;
                    applySearchFilter()
                });

                jQuery(document).on('change', '#subject_id', function (e){
                    let value = $('#subject_id').select2("val");
                    if(value?.length > 0){
                        filter_record['subject_id'] = value[0]?.length > 0 ? value : [];
                    } else {
                        filter_record['subject_id'] = [];
                    }
                    applySearchFilter()
                });

                jQuery(document).on('change', '#language_id', function (e){
                    let value = $('#language_id').select2("val");
                    if(value?.length > 0){
                        filter_record['language_id'] = value[0]?.length > 0 ? value : [];
                    } else {
                        filter_record['language_id'] = [];
                    }
                    applySearchFilter()
                });

                function applySearchFilter(clearFilter = true){
                    $('.tutors-skeleton').toggleClass('d-none');
                    let params = new URLSearchParams(window.location.search);
                    for (let key in filter_record) {
                        if (filter_record.hasOwnProperty(key)) {
                            if (filter_record[key] && (!Array.isArray(filter_record[key]) || filter_record[key].length > 0)) {
                                params.set(key, filter_record[key]);
                            } else {
                                params.delete(key);
                            }
                        }
                    }
                    let newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.replaceState({}, '', newUrl);
                    clearFilters(clearFilter);
                    Livewire.dispatch('tutorFilters', {filters: filter_record});
                }

                function clearFilters(clearFilter = true) {
                    const allClear = !Object.values(filter_record).some(value => value?.length > 0 );
                    $('#clear_filters').parent().toggleClass('d-none', allClear || !clearFilter);
                }
            });
</script>
@endpush
@endsection
