<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Divusi SSO - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            color: #f8fafc;
        }
        
        /* Header */
        .header {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 700;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 500;
        }
        
        .user-role {
            font-size: 12px;
            color: #94a3b8;
        }
        
        .btn-logout {
            background: transparent;
            border: 1px solid #475569;
            color: #94a3b8;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: #334155;
            color: #f8fafc;
        }
        
        /* Main Content */
        .main {
            padding: 32px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome {
            margin-bottom: 32px;
        }
        
        .welcome h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .welcome p {
            color: #94a3b8;
            font-size: 15px;
        }
        
        /* Module Grid */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .module-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
        }
        
        .module-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        
        .module-icon.cobit { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .module-icon.pmo { background: linear-gradient(135deg, #10b981, #059669); }
        .module-icon.hr { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .module-icon.finance { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .module-icon.inventory { background: linear-gradient(135deg, #ec4899, #db2777); }
        .module-icon.report { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        
        .module-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .module-desc {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.5;
        }
        
        .module-status {
            display: inline-block;
            margin-top: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .module-status.active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        
        .module-status.coming {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }
        
        .module-status.dev {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }
        
        /* Quick Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
        }
        
        .stat-change {
            font-size: 12px;
            margin-top: 4px;
        }
        
        .stat-change.up { color: #34d399; }
        .stat-change.down { color: #f87171; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <div class="logo-icon">🔐</div>
            <span class="logo-text">Divusi SSO</span>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role ?? 'user') }}</div>
                </div>
            </div>
            <button class="btn-logout" onclick="logout()">Logout</button>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main">
        <div class="welcome">
            <h1>Welcome back, {{ explode(' ', Auth::user()->name ?? 'User')[0] }}! 👋</h1>
            <p>Select a module to get started with your work</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="section-title">Overview</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Active Modules</div>
                <div class="stat-value">6</div>
                <div class="stat-change up">↑ 2 new this month</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Tasks Completed</div>
                <div class="stat-value">24</div>
                <div class="stat-change up">↑ 12% from last week</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Team Members</div>
                <div class="stat-value">8</div>
                <div class="stat-change">Online now</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Notifications</div>
                <div class="stat-value">3</div>
                <div class="stat-change">Unread</div>
            </div>
        </div>
        
        <!-- Modules -->
        <div class="section-title">Modules</div>
        <div class="modules-grid">
            <!-- COBIT -->
            <a href="/sso/redirect/cobit" class="module-card">
                <div class="module-icon cobit">📊</div>
                <div class="module-name">COBIT</div>
                <div class="module-desc">Control Objectives for Information Technologies - IT Governance Framework</div>
                <span class="module-status active">Active</span>
            </a>
            
            <!-- PMO -->
            <a href="/sso/redirect/pmo" class="module-card">
                <div class="module-icon pmo">📋</div>
                <div class="module-name">PMO</div>
                <div class="module-desc">Project Management Office - Track and manage all projects in one place</div>
                <span class="module-status active">Active</span>
            </a>
            
            <!-- HR Management -->
            <a href="/sso/redirect/hr" class="module-card">
                <div class="module-icon hr">👥</div>
                <div class="module-name">HR Management</div>
                <div class="module-desc">Human Resource management, attendance, and employee data</div>
                <span class="module-status coming">Coming Soon</span>
            </a>
            
            <!-- Finance -->
            <a href="/sso/redirect/finance" class="module-card">
                <div class="module-icon finance">💰</div>
                <div class="module-name">Finance</div>
                <div class="module-desc">Financial management, budgeting, and expense tracking</div>
                <span class="module-status dev">In Development</span>
            </a>
            
            <!-- Inventory -->
            <a href="/sso/redirect/inventory" class="module-card">
                <div class="module-icon inventory">📦</div>
                <div class="module-name">Inventory</div>
                <div class="module-desc">Asset and inventory management system</div>
                <span class="module-status coming">Coming Soon</span>
            </a>
            
            <!-- Reports -->
            <a href="/sso/redirect/report" class="module-card">
                <div class="module-icon report">📈</div>
                <div class="module-name">Reports</div>
                <div class="module-desc">Generate comprehensive reports and analytics</div>
                <span class="module-status active">Active</span>
            </a>
        </div>
    </main>

    <script>
        async function logout() {
            try {
                const response = await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                window.location.href = '/login';
            } catch (error) {
                window.location.href = '/login';
            }
        }
    </script>
</body>
</html>
