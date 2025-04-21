@extends('mail.layout')

@section('content')
    <p>
        Пользователь {{ $user->first_name }} {{ $user->last_name }} приглашает Вас присоедениться к магазину {{ $shop->title }}.
        Для просмотра уведомления воспользуйтесь данной <a href="{{ $uuid }}">ссылкой</a>
    </p>
@endsection
