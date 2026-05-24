@if(!Auth::user()->email_verified_at) @include('elements.resend-verification-email-box') @endif

@if(getSetting('ai.ai_auto_reply_enabled') && Auth::user()->ai_auto_reply_enabled)
<div class="card py-3 px-3 mb-3">
    <div class="custom-control custom-switch">
        <input
            type="checkbox"
            class="custom-control-input"
            id="ai_auto_reply_toggle"
            {{ (!isset(Auth::user()->settings['ai_auto_reply_paused']) || Auth::user()->settings['ai_auto_reply_paused'] !== 'true') ? 'checked' : '' }}
        >
        <label class="custom-control-label" for="ai_auto_reply_toggle">
            {{ __('Enable AI Auto Reply') }}
        </label>
    </div>
    <div class="mt-2">
        <small class="text-muted">
            {{ __('When enabled, an AI assistant will automatically respond to messages you receive from fans on your behalf.') }}
        </small>
    </div>
</div>
@endif

<form method="POST" action="{{route('my.settings.account.save')}}">
    @csrf
    @if(session('success'))
        <div class="alert alert-success text-white font-weight-bold mt-2" role="alert">
            {{session('success')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="{{__('Close')}}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="form-group">
        <label for="username">{{__('Password')}}</label>
        <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="username" name="password" type="password">
        @if($errors->has('password'))
            <span class="invalid-feedback" role="alert">
                <strong>{{$errors->first('password')}}</strong>
            </span>
        @endif
    </div>

    <div class="form-group">
        <label for="username">{{__('New password')}}</label>
        <input class="form-control {{ $errors->has('new_password') ? 'is-invalid' : '' }}" id="username" name="new_password" type="password">
        @if($errors->has('new_password'))
            <span class="invalid-feedback" role="alert">
                <strong>{{$errors->first('new_password')}}</strong>
            </span>
        @endif
    </div>

    <div class="form-group">
        <label for="username">{{__('Confirm password')}}</label>
        <input class="form-control {{ $errors->has('confirm_password') ? 'is-invalid' : '' }}" id="username" name="confirm_password" type="password">
        @if($errors->has('confirm_password'))
            <span class="invalid-feedback" role="alert">
                <strong>{{$errors->first('confirm_password')}}</strong>
            </span>
        @endif
    </div>
    <button class="btn btn-primary btn-block rounded mr-0" type="submit">{{__('Save')}}</button>

</form>
