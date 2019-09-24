<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Результаты</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Auth Token -->
    @if (session('token'))
        <meta name="token" content="{{ session('token') }}">
    @endif

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div>
    <div class="container">
        <table class="table">
            <thead>
            <th scope="col">
                №
            </th>
            <th scope="col">
                Наименование
            </th>
            <th scope="col">
                Поб.
            </th>
            <th scope="col">
                Бух.
            </th>
            <th scope="col">
                М.Бух.
            </th>
            <th scope="col">
                Разн.
            </th>
            <th scope="col">
                Ран.
            </th>
            </thead>
            <tbody>
            @foreach ($teams as $team)
            <tr v-for="(value, index) in teams" :key="index">
                <td>
                                        {{ $loop->index + 1}}
                </td>
                <td>
                    {{ $team->name }}
                </td>
                <td>
                    {{ $team->winner }}
                </td>
                <td> {{ $team->buhgolc }}</td>
                <td> {{ $team->mega_buhgolc }}</td>
                <td> {{ $team->points }}</td>
                <td> {{ $team->rank }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>


