<x-layouts.app :title="$title" :breadcrumbs="$breadcrumbs">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_depan">Nama Depan</label>
                    <input type="text" class="form-control @error('firstName') is-invalid @enderror" value="{{ old('nama_depan') }}" id="nama_depan" name="nama_depan">
                    @error('firstName')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nama_belakang">Nama Belakang</label>
                    <input type="text" class="form-control @error('lastName') is-invalid @enderror" value="{{ old('nama_belakang') }}" id="nama_belakang" name="nama_belakang">
                    @error('lastName')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" id="email" name="email">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <button class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</x-layouts.app>