@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Reset Password') }}</div>

                    <div class="card-body">
                       <form method="POST" action="{{ route('password.update')}}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            
                            <!-- Email -->
                            <label> Email </label>
                            <input type="email" name="email" required>
                            <br/>
                            
                            <!-- New Password -->
                            <label> new Password </label>
                            <input type="password" name="password" required>
                            <br/>
                            
                            <!-- Confirm Password -->
                            <label> new Password Confirmation</label>
                            <input type="password" name="password_confirmation" required>
                            <br/>
                            
                            <button type="submit">Reset Password</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection