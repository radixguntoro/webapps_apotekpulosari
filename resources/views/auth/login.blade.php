@extends('layouts.loginLayout')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center h-100">
        <div class="col-md-8">
            <div class="card card-login elevation-smooth1 border-0 rounded">
                <div class="row">
                    <div class="col pr-0">
                        <div
                            class="card-body bg-gradient-primary rounded-left p-5 text-center d-flex align-items-center justify-content-center h-100">
                            <div class="box-logo">
                                <img src="{{ asset('backend/img/logo/logo-white.png') }}" alt="" width="100"
                                    class="mb-4">
                                <h5 class="text-white mb-0">Apotek Pulosari</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col pl-0">
                        <div class="card-header text-center bg-transparent border-0 pt-5 pb-0">
                            <h2 class="mb-0 font-weight-bold">{{ __('Login') }}</h2>
                        </div>
                        <div class="card-body p-4">
                            {{-- @csrf --}}
                            <div class="form-group">
                                <label for="username" class="caption font-weight-bold">{{ __('Username') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0" id="inputGroupPrepend"><i class="mdi mdi-account mdi-20px text-gray-600"></i></span>
                                    </div>
                                    <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}" autofocus autocomplete="off" placeholder="Username">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password" class="caption font-weight-bold">{{ __('Password') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0" id="inputGroupPrepend"><i class="mdi mdi-lock-outline mdi-20px text-gray-600"></i></span>
                                    </div>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                                    autocomplete="current-password" placeholder="Password">
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-12 text-right">
                                    <button onclick="login(this, event)" id="btn-login"
                                        class="btn btn-gradient-success d-inline-flex align-items-center px-4">
                                        <span class="text-login">{{ __('Login') }}</span>
                                        <span class="text-loading d-none">{{ __('Process') }}</span>
                                        <i class="mdi mdi-login mdi-20px ml-2"></i>
                                        <i class="mdi mdi-loading mdi-20px ml-2 mdi-spin d-none"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    function disableForm(elm) {
        $(elm).attr("disabled", true);
        $(".text-login").addClass("d-none");
        $(".mdi-login").addClass("d-none");
        $(".text-loading").removeClass("d-none");
        $(".mdi-loading").removeClass("d-none");
        $("input").attr("disabled", true);
    };

    function enableForm(elm) {
        $(elm).attr("disabled", false);
        $(".text-login").removeClass("d-none");
        $(".mdi-login").removeClass("d-none");
        $(".text-loading").addClass("d-none");
        $(".mdi-loading").addClass("d-none");
        $("input").attr("disabled", false);
    };

    function login(elm, e) {
        $(elm).attr("disabled", true);
        disableForm(elm);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        e.preventDefault();
        let username = $("input[name=username]").val();
        let password = $("input[name=password]").val();

        if (username == '') {
            enableForm(elm);
            toastr.error('Username harus diisi.', 'Gagal!');
            return;
        } else if (password == '') {
            enableForm(elm);
            toastr.error('Password harus diisi.', 'Gagal!');
            return;
        } else {
            $.ajax({
                type: 'POST',
                url: "{{ route('login') }}",
                data: {
                    password: password,
                    username: username
                },
                success: function (resp) {
                    if (resp.status == 1) {
                        setTimeout(function () {
                            toastr.success('Login berhasil.', 'Sukses!');
                            location.href = "{{ route('dashboard') }}";
                        }, 1500);
                    } else {
                        enableForm(elm);
                        toastr.error('Login gagal.', 'Gagal!');
                    }
                }
            });
        }
    };

</script>
