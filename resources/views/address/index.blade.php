@extends('layouts.admin')

@section('Addresses')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Saved Addresses</h1>

    @if($addresses->isEmpty())
        <p>No addresses found.</p>
    @else
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Province</th>
                    <th>City</th>
                    <th>Subdistrict</th>
                    <th>Street Address</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($addresses as $address)
                    <tr>
                        <td>{{ $address->id }}</td>
                        <td>{{ $address->province }}</td>
                        <td>{{ $address->city }}</td>
                        <td>{{ $address->subdistrict }}</td>
                        <td>{{ $address->street_address }}</td>
                        <td>{{ $address->user->name ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
