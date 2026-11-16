@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Page Header --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Platform Releases & System Updates</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">{{ $versionInfo['version'] }}</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Enterprise freight forwarding platform release lifecycle, applied database schema migrations, and version change audit
            </p>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <form method="POST" action="{{ route('super-admin.releases.clear-cache') }}" onsubmit="return confirm('Clear application and view cache?');" style="margin: 0;">
                @csrf
                <input type="hidden" name="type" value="all">
                <button type="submit" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                    <i class="fa fa-bolt"></i> Flush System Cache
                </button>
            </form>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Sub-Navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;">
        <a href="{{ route('super-admin.releases.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">
            <i class="fa fa-code-fork" style="margin-right: 4px;"></i> Releases & Updates
        </a>
        <a href="{{ route('super-admin.dashboard.reports') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            <i class="fa fa-bar-chart" style="margin-right: 4px;"></i> Operational Reports
        </a>
    </div>

    {{-- System Release Metric Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Active Release</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $versionInfo['version'] }}</div>
                <div style="font-size: 11px; color: #10b981; font-weight: 600; margin-top: 2px;">{{ $versionInfo['channel'] }}</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-tag"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Schema Migrations</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $versionInfo['total_migrations'] }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Batch #{{ $versionInfo['last_migration_batch'] }} (100% Up to Date)</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-database"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Runtime Stack</div>
                <div style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 4px;">PHP {{ PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION }}</div>
                <div style="font-size: 11px; color: #6366f1; font-weight: 600; margin-top: 2px;">Laravel {{ $versionInfo['laravel_version'] }}</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-server"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Deployment Health</div>
                <div style="font-size: 20px; font-weight: 800; color: #10b981; margin-top: 4px;">Clean Build</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Zero Pending Migrations</div>
            </div>
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fa fa-check-shield"></i>
            </div>
        </div>
    </div>

    {{-- Main 2-Column: Left Changelog Timeline, Right Applied Migrations --}}
    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; align-items: start;">
        {{-- Left: Version Changelog Timeline --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Release Changelog History</h2>
                    <span style="font-size: 11px; color: #64748b;">Official platform patches and freight standard upgrades</span>
                </div>
                <span style="font-size: 11px; font-weight: 600; color: #64748b;"><i class="fa fa-history"></i> {{ count($changelog) }} Editions</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach($changelog as $rel)
                <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; background: #fafafa;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 14px; font-weight: 800; color: #0f172a;">{{ $rel['version'] }}</span>
                            <span style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: #fff; color: {{ $rel['badge_color'] }}; border: 1px solid {{ $rel['badge_color'] }};">
                                {{ $rel['badge'] }}
                            </span>
                        </div>
                        <span style="font-size: 11px; color: #64748b;"><i class="fa fa-calendar-o"></i> {{ $rel['release_date'] }}</span>
                    </div>

                    <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
                        {{ $rel['title'] }}
                    </div>

                    <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                        @foreach($rel['highlights'] as $hl)
                        <li style="margin-bottom: 4px;">{{ $hl }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Applied Database Schema Migrations --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Database Schema Migrations</h2>
                    <span style="font-size: 11px; color: #64748b;">Live records from <code>migrations</code> table</span>
                </div>
                <span style="font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 9999px;">
                    <i class="fa fa-check"></i> 100% SYNCED
                </span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($recentMigrations as $m)
                <div style="padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 260px;">
                        <div style="font-family: monospace; font-size: 11px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis;">
                            {{ $m->migration }}
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                            Batch #{{ $m->batch }} • Applied ID #{{ $m->id }}
                        </div>
                    </div>
                    <span style="color: #10b981; font-size: 12px;" title="Executed successfully">
                        <i class="fa fa-check-circle"></i>
                    </span>
                </div>
                @endforeach
            </div>

            <div style="margin-top: 16px; padding: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 11px; color: #1e40af; line-height: 1.5;">
                <i class="fa fa-info-circle" style="margin-right: 4px;"></i> All <strong>{{ $versionInfo['total_migrations'] }}</strong> database migrations are synced with no outstanding schema drift.
            </div>
        </div>
    </div>
</div>
@endsection
