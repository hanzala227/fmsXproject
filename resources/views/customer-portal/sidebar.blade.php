<aside style="background: #405189; color: #fff; height: 100vh; display: flex; flex-direction: column;">
    <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <h2 style="margin: 0; font-size: 18px; font-weight: 700; letter-spacing: 1px; font-family: 'Oswald', sans-serif;">FREIGHTX</h2>
    </div>
    
    <nav style="flex: 1; padding: 20px 0; overflow-y: auto;" class="custom-scrollbar">
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px;">
            <li>
                <a href="{{ route('customer.dashboard') }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; transition: all 0.2s; {{ request()->routeIs('customer.dashboard') ? 'background: rgba(255,255,255,0.1); color: #fff; border-left: 3px solid #fff;' : 'border-left: 3px solid transparent;' }}">
                    <i class="fa fa-dashboard" style="width: 16px; text-align: center;"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('customer.shipments.index') }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; transition: all 0.2s; {{ request()->routeIs('customer.shipments.*') ? 'background: rgba(255,255,255,0.1); color: #fff; border-left: 3px solid #fff;' : 'border-left: 3px solid transparent;' }}">
                    <i class="fa fa-ship" style="width: 16px; text-align: center;"></i>
                    My Shipments
                </a>
            </li>
            <li>
                <a href="{{ route('client.tracking') }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; transition: all 0.2s; {{ request()->routeIs('client.tracking') ? 'background: rgba(255,255,255,0.1); color: #fff; border-left: 3px solid #fff;' : 'border-left: 3px solid transparent;' }}">
                    <i class="fa fa-map-marker" style="width: 16px; text-align: center;"></i>
                    Track & Trace
                </a>
            </li>
        </ul>
    </nav>
</aside>
