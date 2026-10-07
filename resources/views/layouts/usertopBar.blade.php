<header class="topbar">
    <button class="burger" id="menuToggle" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
    <div class="search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Search by Shipment No, HBL/HAWB, MBL/MAWB, Container No...">
    </div>
    <div class="top-right">
        <a href="#" class="bell"><i class="bi bi-bell"></i><em>5</em></a>
        <div class="user">
            <span class="avatar"><i class="bi bi-person-fill"></i></span>
            <div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
            <i class="bi bi-chevron-down"></i>
            <div class="border-t border-gray-100 mt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-left px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <i class="fa fa-power-off w-4 text-center mr-2.5 text-gray-400"></i> LOGOUT SYSTEM
                        </button>
                    </form>
                </div>
        </div>
    </div>
</header>