@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Add Category</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <button type="submit" class="btn btn-success">Add Category</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
