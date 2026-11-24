<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Certificate') }} — {{ $title }}</title>
    <style>
        @page { size: 930px 600px; margin: 0; }
        body { margin: 0; padding: 0; font-family: sans-serif; }
        .certificate-body { width: 930px; height: 600px; background: #f5f5f5; position: relative; overflow: hidden; }
        .draggable-element { position: absolute; }
        #title { font-size: 22px; font-weight: bold; color: #000; left: 50%; transform: translateX(-50%); width: 730px; text-align: center; top: {{ ($positions['title']['y'] ?? 10) }}%; }
        #sub_title { font-size: 18px; color: #000; left: 50%; transform: translateX(-50%); width: 730px; text-align: center; top: {{ ($positions['sub_title']['y'] ?? 22) }}%; }
        #description { font-size: 15px; color: #333; left: 50%; transform: translateX(-50%); width: 730px; text-align: center; top: {{ ($positions['description']['y'] ?? 40) }}%; }
        #student_name { font-size: 26px; font-weight: bold; color: #0b3d91; left: 50%; transform: translateX(-50%); width: 730px; text-align: center; top: {{ ($positions['student_name']['y'] ?? 55) }}%; }
        #course_name { font-size: 18px; color: #000; left: 50%; transform: translateX(-50%); width: 730px; text-align: center; top: {{ ($positions['course_name']['y'] ?? 68) }}%; }
    </style>
</head>
<body>
    <div class="certificate-body">
        @if($builder?->image)
            <img src="{{ asset($builder->image) }}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
        @endif
        <div class="draggable-element" id="title">{{ $title }}</div>
        <div class="draggable-element" id="sub_title">{{ $subTitle }}</div>
        <div class="draggable-element" id="description">{{ $description }}</div>
        <div class="draggable-element" id="student_name">{{ $user->name }}</div>
        <div class="draggable-element" id="course_name">{{ $course->title ?? '' }}</div>
    </div>
</body>
</html>
