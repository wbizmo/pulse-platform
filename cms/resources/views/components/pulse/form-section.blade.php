@props(['title','description'=>null])<fieldset><legend>{{ $title }}</legend>@if($description)<p class="p-help">{{ $description }}</p>@endif<div class="p-form">{{ $slot }}</div></fieldset>
