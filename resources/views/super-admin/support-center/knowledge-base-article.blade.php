@extends('super-admin.layout')

@section('content')
<div style="padding: 16px 20px;">
    {{-- Breadcrumb & Top Bar --}}
    <div style="margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
            <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <i class="fa fa-book"></i> Knowledge Base
            </a>
            <i class="fa fa-chevron-right" style="font-size: 9px; color: #94a3b8;"></i>
            <span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; font-weight: 600; font-size: 10px;">
                {{ $article->category }}
            </span>
            <i class="fa fa-chevron-right" style="font-size: 9px; color: #94a3b8;"></i>
            <span style="color: #1e293b; font-weight: 600; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $article->title }}
            </span>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('super-admin.support-center.knowledge-base') }}" style="height: 32px; padding: 0 14px; font-size: 12px; font-weight: 500; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-arrow-left"></i> All Articles
            </a>
            <form method="POST" action="{{ route('super-admin.support-center.knowledge-base.destroy', $article) }}" onsubmit="return confirm('Are you sure you want to delete this SOP article?');" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" style="height: 32px; padding: 0 12px; font-size: 12px; font-weight: 500; color: #dc2626; background: #fff; border: 1px solid #fecaca; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Delete this article">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Main 2-Column Layout --}}
    <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 20px; align-items: start;">
        {{-- Left: Article Body --}}
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            {{-- Category & Metadata Header --}}
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                <span style="font-size: 11px; font-weight: 700; background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-bookmark-o" style="margin-right: 4px;"></i> {{ $article->category }}
                </span>
                <span style="font-size: 11px; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-calendar-o"></i> Updated {{ $article->updated_at ? $article->updated_at->format('M d, Y') : 'N/A' }}
                </span>
                <span style="font-size: 11px; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-eye"></i> {{ number_format($article->views_count) }} views
                </span>
                @if($article->author)
                <span style="font-size: 11px; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fa fa-user-circle"></i> {{ $article->author->name }}
                </span>
                @endif
            </div>

            {{-- Title --}}
            <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 14px; line-height: 1.35; letter-spacing: -0.3px;">
                {{ $article->title }}
            </h1>

            @if(!empty($article->summary))
            {{-- Executive Summary Box --}}
            <div style="margin-bottom: 24px; padding: 14px 18px; background: #f8fafc; border-left: 4px solid #0ab39c; border-radius: 0 8px 8px 0; font-size: 13px; color: #334155; line-height: 1.6; font-style: italic;">
                <strong style="color: #0f172a; font-style: normal; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Executive Summary:</strong>
                {{ $article->summary }}
            </div>
            @endif

            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;">

            {{-- Full Article Content --}}
            <div style="font-size: 14px; line-height: 1.75; color: #334155; white-space: pre-wrap; font-family: inherit;">
                {{ $article->content }}
            </div>

            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 30px 0 20px;">

            {{-- Footer Feedback Card --}}
            <div style="padding: 16px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #1e293b;">Was this SOP helpful?</div>
                    <div style="font-size: 11px; color: #64748b;">Help us improve multi-tenant operational freight documentation</div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="alert('Thank you for your feedback!')" style="height: 30px; padding: 0 12px; font-size: 11px; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;">
                        <i class="fa fa-thumbs-up"></i> Yes, helpful
                    </button>
                    <button type="button" onclick="alert('Thank you, we will review this SOP.')" style="height: 30px; padding: 0 12px; font-size: 11px; font-weight: 600; color: #64748b; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer;">
                        <i class="fa fa-thumbs-down"></i> Needs update
                    </button>
                </div>
            </div>
        </div>

        {{-- Right: Sidebar (Metadata & Related SOPs) --}}
        <div>
            {{-- SOP Metadata Card --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 20px;">
                <h3 style="font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                    <i class="fa fa-info-circle" style="color: #0ab39c; margin-right: 4px;"></i> SOP Metadata
                </h3>
                <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Category:</span>
                        <span style="font-weight: 600; color: #0f172a;">{{ $article->category }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Standard:</span>
                        <span style="font-weight: 600; color: #0ab39c;">IATA / FIATA / IMO</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Target Audience:</span>
                        <span style="font-weight: 600; color: #0f172a;">Tenant Operators</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Status:</span>
                        <span style="font-weight: 600; color: #10b981;">Published</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Created:</span>
                        <span style="color: #334155;">{{ $article->created_at ? $article->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Related SOPs in Same Category --}}
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px;">
                <h3 style="font-size: 12px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                    <i class="fa fa-folder-open" style="color: #3b82f6; margin-right: 4px;"></i> Related In Category
                </h3>

                @if($relatedArticles->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($relatedArticles as $rel)
                    <a href="{{ route('super-admin.support-center.knowledge-base.show', $rel) }}" style="text-decoration: none; display: block; padding: 8px; border-radius: 6px; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <div style="font-size: 12px; font-weight: 600; color: #1e293b; line-height: 1.4; margin-bottom: 4px;">
                            {{ $rel->title }}
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                            <span><i class="fa fa-eye"></i> {{ $rel->views_count }}</span>
                            <span>&bull;</span>
                            <span>{{ $rel->updated_at ? $rel->updated_at->format('M d, Y') : '' }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div style="font-size: 11px; color: #94a3b8; font-style: italic;">No other SOPs in this category yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
