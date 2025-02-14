@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Upload File to MongoDB</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="filename" class="form-label">File Name</label>
            <input type="text" class="form-control @error('filename') is-invalid @enderror" id="filename" name="filename" value="{{ old('filename') }}" required>
            @error('filename')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Select File</label>
            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
            @error('file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
