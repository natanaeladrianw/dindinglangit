@extends('layouts.master')

@section('content')
    <div class="card p-4">
        <a href="{{ url()->previous() }}" class="btn btn-dark mb-4" style="width: fit-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
        <h3 class="text-black fw-bold">Edit User</h3>
        <form action="{{ route('user.update', $user->id) }}" method="post">
            @csrf
            @method('put')
            <label for="role">Role</label>
            @php
                $roles = [
                    'kasir' => 'Kasir',
                    'admin_dapur' => 'Admin Dapur',
                    'admin_gudang' => 'Admin Gudang',
                    'owner' => 'Owner'
                ];
            @endphp
            <select class="form-select mb-3" name="role" id="role">
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" {{ old('role', $user->role ?? '') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <label for="">Nama</label>
            <input type="text" name="name" class="form-control mb-3" value="{{ $user->name }}">
            <label for="">Username</label>
            <input type="text" name="username" class="form-control mb-3" value="{{ $user->username }}">
            <label for="">Email</label>
            <input type="email" name="email" class="form-control mb-3" value="{{ $user->email }}">
            <label for="">Password</label>
            <input type="password" name="password" placeholder="Password" class="form-control mb-3">
            <label for="">Konfirmasi Password</label>
            <input class="w-100 form-control mb-3" name="password_confirmation" type="password" placeholder="Confirm Password" required>
            <div class="text-end">
                <button type="submit" class="btn btn-dark">Save</button>
            </div>
        </form>
    </div>
@endsection
