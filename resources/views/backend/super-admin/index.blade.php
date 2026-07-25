@extends('backend.layouts.app')

@section('title', 'Super Admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Super Admin</h1>
        <a href="{{ route('backend.super-admin.create') }}" class="btn btn-primary">Nuovo Super Admin</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.super-admin.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('backend.super-admin.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo super admin?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
