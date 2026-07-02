@if (session('status'))
    <div class="mb-lg rounded-md bg-success-pale text-success-deep text-body-sm px-md py-sm">
        @switch(session('status'))
            @case('profile-updated')
                Profil berhasil diperbarui.
                @break
            @case('password-updated')
                Kata sandi berhasil diubah.
                @break
            @default
                {{ session('status') }}
        @endswitch
    </div>
@endif

@if ($errors->any())
    <div class="mb-lg rounded-md bg-error/10 text-error text-body-sm px-md py-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
