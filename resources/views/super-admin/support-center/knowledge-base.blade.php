@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;" x-data="{ showCreateModal: false }">
    {{-- Header --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Knowledge Base & SOP Library</h1>
                <span style="font-size: 10px; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Freight Documentation</span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">
                Standard operating procedures, carrier integration guides, and regulatory freight compliance articles
            </p>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.support-center.index') }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-life-ring"></i> Support Tickets
            </a>
            <button @click="showCreateModal = true" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#089784'" onmouseout="this.style.background='#0ab39c'">
                <i class="fa fa-plus"></i> New Article
            </button>
        </div>
    </div>

    {{-- Session Flash Notifications --}}
    @if(session('success'))
    <div style="margin-bottom: 16px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 12px; display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #065f46; cursor: pointer;"><i class="fa fa-times"></i></button>
    </div>
    @endif

    {{-- Sub-navigation Tabs --}}
    <div style="display: flex; gap: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 16px;">
        <a href="{{ route('super-admin.support-center.index') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            All Tickets
        </a>
        <a href="{{ route('super-admin.support-center.my-tickets') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            My Tickets
        </a>
        <a href="{{ route('super-admin.support-center.sla-management') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 500; color: #64748b;">
            SLA Management
        </a>
        <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="text-decoration: none; padding-bottom: 8px; font-size: 13px; font-weight: 600; color: #0ab39c; border-bottom: 2px solid #0ab39c;">
            Knowledge Base
        </a>
    </div>

    {{-- KPI Cards --}}
    @if(isset($kpis))
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Published SOPs</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $kpis['total_articles'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Ready for operators & tenants</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-book"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Knowledge Categories</div>
                <div style="font-size: 22px; font-weight: 800; color: #6366f1; margin-top: 4px;">{{ $kpis['total_categories'] }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Air, Ocean, Port, Invoicing</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-folder-open"></i>
            </div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">Operator Article Views</div>
                <div style="font-size: 22px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ number_format($kpis['total_views']) }}</div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Platform-wide readership</div>
            </div>
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="fa fa-eye"></i>
            </div>
        </div>
    </div>
    @endif

    {{-- Search & Category Filter Bar --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('super-admin.support-center.knowledge-base') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin: 0;">
            <div style="flex: 1; min-width: 240px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles by title, keywords, or regulation..." style="width: 100%; height: 34px; padding: 0 12px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
            </div>

            <div style="min-width: 200px;">
                <select name="category" onchange="this.form.submit()" style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff; color: #334155;">
                    <option value="">All Categories ({{ $kpis['total_articles'] ?? 0 }})</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category }}" {{ request('category') === $cat->category ? 'selected' : '' }}>
                            {{ $cat->category }} ({{ $cat->count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" style="height: 34px; padding: 0 16px; font-size: 12px; font-weight: 600; color: #fff; background: #405189; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fa fa-search"></i> Search
            </button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="height: 34px; padding: 0 10px; font-size: 12px; color: #ef4444; border: 1px solid #fca5a5; border-radius: 4px; display: inline-flex; align-items: center; text-decoration: none;" title="Reset filters">
                    <i class="fa fa-times"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Articles Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @forelse($articles as $art)
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.02); transition: all 0.2s ease;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 4px; text-transform: uppercase;">
                        {{ $art->category }}
                    </span>
                    <span style="font-size: 10px; color: #94a3b8; font-weight: 500;">
                        <i class="fa fa-eye" style="margin-right: 3px;"></i> {{ $art->views_count }} views
                    </span>
                </div>
                <h3 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 8px; line-height: 1.4;">
                    <a href="{{ route('super-admin.support-center.knowledge-base.show', $art) }}" style="color: #1e293b; text-decoration: none;" onmouseover="this.style.color='#0ab39c'" onmouseout="this.style.color='#1e293b'">
                        {{ $art->title }}
                    </a>
                </h3>
                <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0 0 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $art->summary ?: Str::limit(strip_tags($art->content), 120) }}
                </p>
            </div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 10px; color: #94a3b8;">
                    Target: <strong>{{ $art->target_audience }}</strong>
                </span>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <form method="POST" action="{{ route('super-admin.support-center.knowledge-base.destroy', $art) }}" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this article?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 4px;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'" title="Delete Article">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                    <a href="{{ route('super-admin.support-center.knowledge-base.show', $art) }}" style="font-size: 11px; font-weight: 600; color: #0ab39c; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        Read SOP <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; color: #64748b;">
            <i class="fa fa-book" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
            No articles match your search criteria.
        </div>
        @endforelse
    </div>

    @if($articles->hasPages())
    <div style="padding: 12px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
        {{ $articles->links() }}
    </div>
    @endif

    {{-- Create Article Modal --}}
    <div x-show="showCreateModal" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div @click.away="showCreateModal = false" style="background: #fff; border-radius: 8px; width: 100%; max-width: 650px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="padding: 14px 18px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 14px; font-weight: 700; color: #1e293b;">
                    <i class="fa fa-plus-circle" style="color: #0ab39c; margin-right: 6px;"></i> Publish New Help Article / SOP
                </div>
                <button @click="showCreateModal = false" style="background: none; border: none; font-size: 14px; color: #94a3b8; cursor: pointer;">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('super-admin.support-center.knowledge-base.store') }}" style="padding: 18px;">
                @csrf
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Article Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Ocean FCL Container Free Time and Tariff Rules" style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Category *</label>
                        <select name="category" required style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                            <option value="Ocean Freight & B/L">Ocean Freight & B/L</option>
                            <option value="Air Cargo & AWB">Air Cargo & AWB</option>
                            <option value="Customs & Demurrage">Customs & Demurrage</option>
                            <option value="API Integrations">API Integrations</option>
                            <option value="Accounting & Billing">Accounting & Billing</option>
                            <option value="Platform Security">Platform Security</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Target Audience *</label>
                        <select name="target_audience" required style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; background: #fff;">
                            <option value="All">All Audiences</option>
                            <option value="Operators">Freight Operators</option>
                            <option value="Tenant Admin">Tenant Admins</option>
                            <option value="SuperAdmin">SuperAdmin Only</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Summary / Short Excerpt</label>
                    <input type="text" name="summary" placeholder="Brief 1-2 sentence overview shown on article cards..." style="width: 100%; height: 34px; padding: 0 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 4px;">Article Body / SOP Instructions *</label>
                    <textarea name="content" rows="6" required placeholder="Detailed procedural steps, compliance notes, or troubleshooting guidelines..." style="width: 100%; padding: 8px 10px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; outline: none; box-sizing: border-box;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" @click="showCreateModal = false" style="height: 34px; padding: 0 16px; font-size: 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: #fff; color: #475569; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" style="height: 34px; padding: 0 20px; font-size: 12px; font-weight: 600; color: #fff; background: #0ab39c; border: none; border-radius: 4px; cursor: pointer;">
                        Publish Article
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
