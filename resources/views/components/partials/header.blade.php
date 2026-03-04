<header class="header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">{{ $pageTitle ?? 'Dashboard' }}</h1>
    </div>
    
    <div class="header-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Filter projects, status, services...">
            <span class="search-pill">Filter</span>
        </div>
        
        <div class="header-actions">
            <button class="header-btn notification-btn">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </button>
            
            <button class="header-btn theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
            </button>
            
            <div class="user-menu">
                <img src="https://ui-avatars.com/api/?name=Developer&background=667eea&color=fff" alt="User">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>
</header>

<style>
.header {
    height: 74px;
    background: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid #d8e0ef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.07);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(8px);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.menu-toggle {
    display: none;
    background: linear-gradient(145deg, #ffffff, #f2f5fb);
    border: 1px solid #d8e0ef;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    font-size: 18px;
    cursor: pointer;
    color: #173b6a;
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #0b1222;
    letter-spacing: -0.02em;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fbff;
    border: 1px solid #d8e0ef;
    padding: 10px 14px;
    border-radius: 14px;
    width: 340px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-box:focus-within {
    border-color: rgba(31, 78, 140, 0.42);
    box-shadow: 0 0 0 4px rgba(31, 78, 140, 0.12);
}

.search-box i { color: #6b7891; }

.search-box input {
    border: none;
    background: none;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: #182338;
    font-weight: 500;
}

.search-box input::placeholder {
    color: #8893a7;
}

.search-pill {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #173b6a;
    background: rgba(31, 78, 140, 0.1);
    border: 1px solid rgba(31, 78, 140, 0.2);
    border-radius: 999px;
    padding: 4px 8px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-btn {
    position: relative;
    background: linear-gradient(145deg, #ffffff, #f2f5fb);
    border: 1px solid #d8e0ef;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #4a5670;
}

.header-btn:hover {
    color: #fff;
    background: linear-gradient(145deg, #1f4e8c, #173b6a);
    border-color: transparent;
    transform: translateY(-1px);
    box-shadow: 0 10px 18px rgba(31, 78, 140, 0.28);
}

.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #b42318;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    border: 1px solid #ffffff;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 12px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.user-menu:hover {
    background: #f8fbff;
    border-color: #d8e0ef;
}

.user-menu img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 2px solid rgba(234, 179, 70, 0.5);
}

@media (max-width: 768px) {
    .menu-toggle { display: block; }
    .search-box { display: none; }
}
</style>
