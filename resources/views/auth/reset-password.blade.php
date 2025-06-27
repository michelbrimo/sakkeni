@extends('layouts.app') 

@section('content')
    <div class="container">
        <div class="row justify-content-center" style="margin:1rem; width: 95%;">
            <div class="col-md-8">
                <div class="card" style="border: none;" >
                    <div class="card-header" style="background:#FCFDFD; margin-left: 1rem; border-bottom:none; font-family: Poppins, sans-serif; font-weight:600;">{{ __('Reset Password') }}</div>
                    <div style="background-color: #101818; height: 1px; width: 80%; margin-left:2rem"></div>

                    <div style="padding:1rem;">
                       <form method="POST" action="{{ route('password.update')}}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            
                            <!-- Email -->
                            <label style="width:25%; margin:1rem 0 0.1rem 1rem; color:#101818; font-weight:600; font-size:0.85rem" > Email </label>
                            <input style="width:71%; margin-left:1rem; border-radius:10px; height:34px; border: 1px solid #CBCBCB; padding: 1.3rem 0.6rem; font-family: Poppins, sans-serif;" type="email" name="email" required readonly value="{{ $email }}">
                            <br/>
                            
                            <!-- New Password -->
                            <label style="width:25%; margin:2rem 0 0.1rem 1rem; color:#101818; font-weight:600; font-size:0.85rem"> New Password </label>
                            <input style="width:71%; margin-left:1rem; border-radius:10px; height:34px; border: 1px solid #CBCBCB; padding: 1.3rem 0.6rem; font-family: Poppins, sans-serif;" type="password" name="password" required>
                            <br/>
                            
                            <!-- Confirm Password -->
                            <label style="width:25%; margin:2rem 0 0.1rem 1rem; color:#101818; font-weight:600; font-size:0.85rem"> Password Confirmation</label>
                            <input style="width:71%; margin-left:1rem; border-radius:10px; height:34px; border: 1px solid #CBCBCB; padding: 1.3rem 0.6rem; font-family: Poppins, sans-serif;" type="password" name="password_confirmation" required>
                            <br/>
                            
                            <button type="submit" style="padding:0.5rem 1rem 0.5rem 1rem; margin:3rem 1rem; border-radius:5px; background:#27393A; color:#FCFDFD; font-weight:400; font-size:0.8rem">Reset Password</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection