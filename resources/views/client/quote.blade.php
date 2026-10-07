@extends('layouts.userapp')

@section('content')

<div class="client-page">

    {{-- Page Header --}}
    <div class="client-page-header">
        <div>
            <h1>My Quotes</h1>
            <p>View and manage your quotations.</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="client-filter-box">
        <form method="GET" class="client-filter-form">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Quote no / commodity"
                class="client-input"
            >

            <select name="status" class="client-select">
                <option value="">All Status</option>

                @foreach($statuses as $s)
                    <option
                        value="{{ $s }}"
                        @selected(request('status') === $s)
                    >
                        {{ $s }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="client-btn">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a
                    href="{{ route('cp.quotes.index') }}"
                    class="client-clear-btn"
                >
                    Clear
                </a>
            @endif

        </form>
    </div>

    {{-- Quotes Table --}}
    <div class="client-table-card">

        <div class="client-card-header">
            <div>
                <h2>Quotes</h2>
                <p>Your available quotations</p>
            </div>
        </div>

        <div class="client-table-wrapper">

            <table class="client-table">

                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Date</th>
                        <th>Valid Till</th>
                        <th>Mode</th>
                        <th>Route</th>
                        <th>Commodity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($quotes as $q)

                        <tr>

                            <td>
                                <strong>
                                    {{ $q->quote_no }}
                                </strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($q->quote_date)->format('d M Y') }}
                            </td>

                            <td>
                                @if($q->expiry_date)
                                    {{ \Carbon\Carbon::parse($q->expiry_date)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $q->transport_mode ?? '-' }}
                            </td>

                            <td>
                                <span class="client-route">
                                    {{ $q->pol_code ?? '-' }}
                                    →
                                    {{ $q->pod_code ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $q->commodity ?? '-' }}
                            </td>

                            <td>
                                <span class="client-status">
                                    {{ $q->status }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('cp.quotes.show', $q->id) }}"
                                    class="client-view-link"
                                >
                                    View
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">

                                <div class="client-empty">

                                    <div class="client-empty-title">
                                        No Quotes Found
                                    </div>

                                    <div class="client-empty-text">
                                        There are no quotations available for your company.
                                    </div>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if($quotes->hasPages())
            <div class="client-pagination">
                {{ $quotes->links() }}
            </div>
        @endif

    </div>

</div>

@endsection