@extends('super-admin.layout')

@section('content')
<div style="padding: 16px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-size: 16px; font-weight: 600; color: #1e293b;">
            <i class="fa fa-book" style="color: #405189; margin-right: 6px;"></i> Knowledge Base
        </div>
        
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('super-admin.support-center.index') }}" style="height: 32px; padding: 0 16px; font-size: 12px; font-weight: 500; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fa fa-list" style="margin-right: 6px;"></i> All Tickets
            </a>
        </div>
    </div>

    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 40px; text-align: center;">
        <div style="margin-bottom: 20px;">
            <i class="fa fa-Tools" style="font-size: 48px; color: #cbd5e1;"></i>
        </div>
        <h2 style="font-size: 20px; font-weight: 600; color: #1e293b; margin-bottom: 12px;">Knowledge Base Module is Under Construction</h2>
        <p style="font-size: 14px; color: #64748b; max-width: 500px; margin: 0 auto; line-height: 1.6;">
            This module will allow you to create, manage, and share help articles, tutorials, and FAQs with your customers and support agents to reduce ticket volume.
        </p>
    </div>
</div>
@endsection
