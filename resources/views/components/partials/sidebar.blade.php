<aside id="sidebar" class="sidebar d-flex flex-column">
    <div class="sidebar-brand p-3 border-bottom border-white border-opacity-10">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-icon">
                    <i class="fab fa-docker"></i>
                </div>
                <div>
                    <span class="brand-title d-block">Docker Gen</span>
                    <small class="brand-subtitle">Premium Workspace</small>
                </div>
            </div>
            <button id="closeSidebar" class="sidebar-close d-md-none" type="button" aria-label="Close sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="sidebar-scroll flex-fill overflow-auto p-3">
        <nav>
            <small class="sidebar-section-label">Main Menu</small>

            <a href="{{ route('dashboard') }}"
               class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>

            <div class="mb-2">
                <a href="#dockerMenu"
                   class="nav-link sidebar-link {{ request()->is('docker*') ? 'active-link' : '' }}"
                   data-bs-toggle="collapse"
                   role="button"
                   aria-expanded="{{ request()->is('docker*') ? 'true' : 'false' }}">
                    <i class="fab fa-docker"></i>
                    <span class="flex-fill">Docker</span>
                    <i class="fas fa-chevron-down menu-arrow"></i>
                </a>

                <div class="collapse {{ request()->is('docker*') ? 'show' : '' }}" id="dockerMenu">
                    <div class="sidebar-sublinks">
                        <a href="{{ route('docker.create') }}"
                           class="nav-link sidebar-sublink {{ request()->routeIs('docker.create') ? 'active-sub' : '' }}">
                            <i class="fas fa-plus-circle"></i>
                            <span>Create Project</span>
                        </a>
                        <a href="{{ route('docker.index') }}"
                           class="nav-link sidebar-sublink {{ request()->routeIs('docker.index') ? 'active-sub' : '' }}">
                            <i class="fas fa-list-ul"></i>
                            <span>Projects List</span>
                        </a>
                    </div>
                </div>
            </div>

            <hr class="sidebar-divider">

            <small class="sidebar-section-label">Resources</small>

            <a href="#" class="nav-link sidebar-link">
                <i class="fas fa-book"></i>
                <span>Documentation</span>
                <i class="fas fa-external-link-alt ms-auto sidebar-link-end"></i>
            </a>

            <a href="#" class="nav-link sidebar-link">
                <i class="fas fa-question-circle"></i>
                <span>Help & Support</span>
            </a>
        </nav>
    </div>

    <div class="p-3 border-top border-white border-opacity-10">
        <div class="profile-card d-flex align-items-center gap-2 p-2 rounded-3">
            <div class="position-relative">
                <img src="https://ui-avatars.com/api/?name=Developer&background=1d4d8f&color=fff"
                     alt="User"
                     class="rounded-circle profile-avatar">
                <span class="profile-dot position-absolute bottom-0 end-0 rounded-circle"></span>
            </div>
            <div class="flex-fill text-truncate">
                <h6 class="text-white mb-0 text-truncate">Developer</h6>
                <small class="profile-role text-truncate d-block">Laravel Expert</small>
            </div>
            <button class="btn btn-sm btn-link profile-more p-1">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </div>
    </div>
</aside>

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    z-index: 1000;
    background: linear-gradient(180deg, #0f1727 0%, #111d35 50%, #0d162a 100%);
    border-right: 1px solid rgba(234, 179, 70, 0.16);
    box-shadow: 18px 0 40px rgba(3, 8, 18, 0.35);
}

.sidebar::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(160deg, rgba(234, 179, 70, 0.06), rgba(56, 111, 199, 0.04) 48%, transparent 78%);
}

.sidebar-brand,
.sidebar-scroll,
.profile-card {
    position: relative;
    z-index: 1;
}

.brand-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f8fafc;
    background: linear-gradient(145deg, #1f4e8c, #173b6a);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14), 0 8px 18px rgba(0, 0, 0, 0.3);
}

.brand-title {
    color: #f8fafc;
    font-size: 1.02rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.brand-subtitle {
    color: rgba(226, 232, 240, 0.68);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.09em;
}

.sidebar-close {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid rgba(248, 250, 252, 0.16);
    color: #dbe5f4;
    background: rgba(255, 255, 255, 0.07);
}

.sidebar-section-label {
    display: block;
    padding: 0 12px;
    margin-bottom: 10px;
    color: rgba(226, 232, 240, 0.56);
    font-size: 0.66rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.11em;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 8px;
    padding: 12px;
    border-radius: 12px;
    color: rgba(226, 232, 240, 0.78);
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.sidebar-link i {
    width: 18px;
    text-align: center;
}

.sidebar-link:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(226, 232, 240, 0.14);
}

.sidebar-link.active-link {
    color: #f8fafc;
    background: linear-gradient(120deg, rgba(31, 78, 140, 0.95), rgba(23, 59, 106, 0.95));
    border-color: rgba(234, 179, 70, 0.35);
    box-shadow: 0 10px 20px rgba(5, 10, 22, 0.38);
}

.sidebar-sublinks {
    margin: 8px 0 0 10px;
    padding-left: 14px;
    border-left: 1px solid rgba(226, 232, 240, 0.2);
}

.sidebar-sublink {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    padding: 9px 12px;
    border-radius: 10px;
    color: rgba(226, 232, 240, 0.74);
    transition: all 0.2s ease;
}

.sidebar-sublink i {
    width: 14px;
    font-size: 0.78rem;
    color: rgba(226, 232, 240, 0.66);
}

.sidebar-sublink:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.06);
}

.sidebar-sublink.active-sub {
    color: #fef7e6;
    background: rgba(234, 179, 70, 0.17);
    border: 1px solid rgba(234, 179, 70, 0.28);
}

.sidebar-sublink.active-sub i {
    color: #f3c66a;
}

.menu-arrow {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.sidebar-link[aria-expanded="true"] .menu-arrow {
    transform: rotate(180deg);
}

.sidebar-divider {
    border-color: rgba(226, 232, 240, 0.2);
    margin: 14px 0;
}

.sidebar-link-end {
    font-size: 12px;
    opacity: 0.55;
}

.profile-card {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(226, 232, 240, 0.16);
}

.profile-avatar {
    width: 44px;
    height: 44px;
    border: 2px solid rgba(234, 179, 70, 0.55);
}

.profile-dot {
    width: 11px;
    height: 11px;
    border: 2px solid #0f1727;
    background: #34d399;
}

.profile-role {
    color: rgba(226, 232, 240, 0.66);
    font-size: 12px;
}

.profile-more {
    color: rgba(226, 232, 240, 0.58);
}

.profile-more:hover {
    color: #f8fafc;
}

.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(226, 232, 240, 0.28) transparent;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(226, 232, 240, 0.28);
    border-radius: 8px;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.active {
        transform: translateX(0);
    }
}
</style>
