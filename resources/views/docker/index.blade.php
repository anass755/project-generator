<x-app-layout>
    <x-slot:title>Docker Projects</x-slot:title>
    <x-slot:pageTitle>My Docker Projects</x-slot:pageTitle>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- Header --}}
                <div class="docker-list-header">
                    <div>
                        <h2 class="docker-list-title">
                            <i class="fab fa-docker me-2"></i>
                            Docker Projects
                        </h2>
                        <p class="docker-list-subtitle">Manage your running Docker environments</p>
                    </div>
                </div>

                {{-- Projects List --}}
                <div class="docker-projects-list">
                    
                    @forelse($projects as $project)
                    <div class="docker-project-card">
                        {{-- Card Header --}}
                        <div class="docker-project-header" onclick="dockerToggleAccordion({{ $project->id }})">
                            <div class="docker-project-info">
                                <div class="docker-project-icon">
                                    <i class="fab fa-docker"></i>
                                </div>
                                <div>
                                    <h5 class="docker-project-name">{{ $project->project_name }}</h5>
                                    <div class="docker-project-meta">
                                        <span class="docker-meta-item">
                                            <i class="fab fa-php"></i> PHP {{ $project->php_version }}
                                        </span>
                                        <span class="docker-meta-item">
                                            <i class="fas fa-database"></i> MySQL {{ $project->mysql_version }}
                                        </span>
                                        <span class="docker-meta-item">
                                            <i class="fas fa-calendar"></i> {{ $project->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="docker-project-actions">
                                <span class="docker-status-badge docker-status-{{ $project->status }}">
                                    <i class="fas fa-circle"></i> {{ ucfirst($project->status) }}
                                </span>
                                <button type="button" class="docker-accordion-toggle" id="dockerToggleBtn{{ $project->id }}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Accordion Content --}}
                        <div class="docker-accordion-content" id="dockerAccordion{{ $project->id }}" style="display: none;">
                            <div class="docker-accordion-body">
                                
                                {{-- Project Details Grid --}}
                                <div class="docker-details-grid">
                                    <div class="docker-detail-item">
                                        <i class="fas fa-globe text-primary"></i>
                                        <div>
                                            <small>Domain</small>
                                            <strong>{{ $project->domain_name }}</strong>
                                        </div>
                                    </div>
                                    <div class="docker-detail-item">
                                        <i class="fas fa-network-wired text-danger"></i>
                                        <div>
                                            <small>App Port</small>
                                            <strong>{{ $project->app_port }}</strong>
                                        </div>
                                    </div>
                                    <div class="docker-detail-item">
                                        <i class="fas fa-table text-warning"></i>
                                        <div>
                                            <small>PHPMyAdmin</small>
                                            <strong>{{ $project->phpmyadmin_port }}</strong>
                                        </div>
                                    </div>
                                    <div class="docker-detail-item">
                                        <i class="fas fa-user text-success"></i>
                                        <div>
                                            <small>DB User</small>
                                            <strong>{{ $project->db_username }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Services --}}
                                @if($project->services && count($project->services) > 0)
                                <div class="docker-services-section">
                                    <h6 class="docker-section-title">
                                        <i class="fas fa-puzzle-piece me-2"></i>Additional Services
                                    </h6>
                                    <div class="docker-services-badges">
                                        @foreach($project->services as $service)
                                        <span class="docker-service-badge docker-service-{{ strtolower($service) }}">
                                            @if($service === 'Redis')
                                                <i class="fas fa-database"></i>
                                            @elseif($service === 'MailHog')
                                                <i class="fas fa-envelope"></i>
                                            @elseif($service === 'Node')
                                                <i class="fab fa-node-js"></i>
                                            @endif
                                            {{ $service }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Live Preview --}}
                                <div class="docker-preview-section">
                                    <h6 class="docker-section-title">
                                        <i class="fas fa-desktop me-2"></i>Live Preview
                                    </h6>
                                    
                                    {{-- Preview Tabs --}}
                                    <div class="docker-preview-tabs">
                                        <button class="docker-preview-tab active" onclick="dockerSwitchPreview({{ $project->id }}, 'app')">
                                            <i class="fas fa-globe me-1"></i> Application
                                        </button>
                                        <button class="docker-preview-tab" onclick="dockerSwitchPreview({{ $project->id }}, 'phpmyadmin')">
                                            <i class="fas fa-database me-1"></i> PHPMyAdmin
                                        </button>
                                        @if(in_array('MailHog', $project->services ?? []))
                                        <button class="docker-preview-tab" onclick="dockerSwitchPreview({{ $project->id }}, 'mailhog')">
                                            <i class="fas fa-envelope me-1"></i> MailHog
                                        </button>
                                        @endif
                                    </div>

                                    {{-- Preview Frame --}}
                                    <div class="docker-preview-container">
                                        <div class="docker-preview-toolbar">
                                            <div class="docker-preview-url">
                                                <i class="fas fa-lock text-success me-2"></i>
                                                <span id="dockerPreviewUrl{{ $project->id }}">http://localhost:{{ $project->app_port }}</span>
                                            </div>
                                            <div class="docker-preview-actions">
                                                <a href="http://localhost:{{ $project->app_port }}" target="_blank" class="docker-preview-btn" title="Open in new tab">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <button class="docker-preview-btn" onclick="dockerRefreshPreview({{ $project->id }})" title="Refresh">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="docker-preview-frame-wrapper">
                                            @if($project->status === 'running')
                                                <iframe 
                                                    id="dockerPreviewFrame{{ $project->id }}" 
                                                    src="http://localhost:{{ $project->app_port }}" 
                                                    class="docker-preview-frame"
                                                    frameborder="0"
                                                ></iframe>
                                            @else
                                                <div class="docker-preview-placeholder">
                                                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                    <h5>Project Not Running</h5>
                                                    <p>Start the project to see the preview</p>
                                                    <button class="btn btn-primary btn-sm mt-2" onclick="dockerStartProject({{ $project->id }})">
                                                        <i class="fas fa-play me-2"></i>Start Project
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="docker-action-buttons">
                                    @if($project->status === 'running')
                                        <button class="btn btn-warning btn-sm" onclick="dockerStopProject({{ $project->id }})">
                                            <i class="fas fa-stop me-2"></i>Stop
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="dockerRestartProject({{ $project->id }})">
                                            <i class="fas fa-redo me-2"></i>Restart
                                        </button>
                                    @else
                                        <button class="btn btn-success btn-sm" onclick="dockerStartProject({{ $project->id }})">
                                            <i class="fas fa-play me-2"></i>Start
                                        </button>
                                    @endif
                                    <button class="btn btn-info btn-sm" onclick="dockerViewLogs({{ $project->id }})">
                                        <i class="fas fa-file-alt me-2"></i>Logs
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="dockerDownloadFiles({{ $project->id }})">
                                        <i class="fas fa-download me-2"></i>Download
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="dockerDeleteProject({{ $project->id }})">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="docker-empty-state">
                        <i class="fab fa-docker fa-4x text-muted mb-3"></i>
                        <h4>No Docker Projects Yet</h4>
                        <p class="text-muted">Create your first Docker project to get started</p>
                        <a href="{{ route('docker.create') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Create Project
                        </a>
                    </div>
                    @endforelse

                </div>

            </div>
        </div>
    </div>

    <style>
        /* Header */
        .docker-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 24px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .docker-list-title {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a2e;
            margin: 0;
        }

        .docker-list-subtitle {
            color: #6b7280;
            margin: 8px 0 0 0;
            font-size: 15px;
        }

        /* Projects List */
        .docker-projects-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Project Card */
        .docker-project-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .docker-project-card:hover {
            box-shadow: 0 6px 30px rgba(0,0,0,0.12);
        }

        /* Project Header */
        .docker-project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .docker-project-header:hover {
            background: #f8f9fa;
        }

        .docker-project-info {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
        }

        .docker-project-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #b90c0c, #510101 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .docker-project-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 8px 0;
        }

        .docker-project-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .docker-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .docker-meta-item i {
            font-size: 14px;
        }

        /* Project Actions */
        .docker-project-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .docker-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .docker-status-running {
            background: #d1fae5;
            color: #065f46;
        }

        .docker-status-running i {
            color: #10b981;
            animation: dockerPulse 2s infinite;
        }

        .docker-status-stopped {
            background: #fee2e2;
            color: #991b1b;
        }

        @keyframes dockerPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .docker-accordion-toggle {
            background: #f3f4f6;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
        }

        .docker-accordion-toggle:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .docker-accordion-toggle.active {
            transform: rotate(180deg);
            background: #667eea;
            color: white;
        }

        /* Accordion Content */
        .docker-accordion-content {
            border-top: 2px solid #f3f4f6;
            animation: dockerSlideDown 0.4s ease;
        }

        @keyframes dockerSlideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 2000px;
            }
        }

        .docker-accordion-body {
            padding: 24px;
        }

        /* Details Grid */
        .docker-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .docker-detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            padding: 16px;
            border-radius: 12px;
        }

        .docker-detail-item i {
            font-size: 24px;
        }

        .docker-detail-item small {
            display: block;
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .docker-detail-item strong {
            display: block;
            font-size: 15px;
            color: #1a1a2e;
            font-weight: 700;
        }

        /* Services Section */
        .docker-services-section {
            margin-bottom: 24px;
        }

        .docker-section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 12px;
        }

        .docker-services-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .docker-service-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .docker-service-redis {
            background: linear-gradient(135deg, #dc2626 0%, #f87171 100%);
        }

        .docker-service-mailhog {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        }

        .docker-service-node {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }

        /* Preview Section */
        .docker-preview-section {
            margin-bottom: 24px;
        }

        .docker-preview-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .docker-preview-tab {
            padding: 10px 20px;
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .docker-preview-tab:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .docker-preview-tab.active {
            background: linear-gradient(135deg, #b90c0c 0%, #510101 100%);
            color: white;
        }

        .docker-preview-container {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
        }

        .docker-preview-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border-bottom: 2px solid #e5e7eb;
        }

        .docker-preview-url {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .docker-preview-actions {
            display: flex;
            gap: 8px;
        }

        .docker-preview-btn {
            background: #f3f4f6;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
            text-decoration: none;
        }

        .docker-preview-btn:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .docker-preview-frame-wrapper {
            position: relative;
            width: 100%;
            height: 600px;
            background: white;
        }

        .docker-preview-frame {
            width: 100%;
            height: 100%;
            border: none;
        }

        .docker-preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: #6b7280;
        }

        .docker-preview-placeholder h5 {
            color: #1a1a2e;
            font-weight: 700;
        }

        /* Action Buttons */
        .docker-action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Empty State */
        .docker-empty-state {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .docker-empty-state h4 {
            color: #1a1a2e;
            font-weight: 700;
            margin-bottom: 12px;
        }
    </style>

    <script>
        function dockerToggleAccordion(projectId) {
            const accordion = document.getElementById('dockerAccordion' + projectId);
            const toggleBtn = document.getElementById('dockerToggleBtn' + projectId);
            
            if (accordion.style.display === 'none') {
                accordion.style.display = 'block';
                toggleBtn.classList.add('active');
            } else {
                accordion.style.display = 'none';
                toggleBtn.classList.remove('active');
            }
        }

        function dockerSwitchPreview(projectId, type) {
            const tabs = document.querySelectorAll(`#dockerAccordion${projectId} .docker-preview-tab`);
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.closest('.docker-preview-tab').classList.add('active');

            const iframe = document.getElementById('dockerPreviewFrame' + projectId);
            const urlSpan = document.getElementById('dockerPreviewUrl' + projectId);

            let url = '';
            if (type === 'app') {
                url = 'http://localhost:{{ $project->app_port ?? 8080 }}';
            } else if (type === 'phpmyadmin') {
                url = 'http://localhost:{{ $project->phpmyadmin_port ?? 8081 }}';
            } else if (type === 'mailhog') {
                url = 'http://localhost:8025';
            }

            if (iframe) {
                iframe.src = url;
            }
            urlSpan.textContent = url;
        }

        function dockerRefreshPreview(projectId) {
            const iframe = document.getElementById('dockerPreviewFrame' + projectId);
                if (iframe) {
                    iframe.src = iframe.src;
                }
            }

        function dockerStartProject(projectId) {
            // Show loading
            const statusBadge = document.querySelector(`.docker-status-badge.docker-status-${projectId}`);
            statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
            
            fetch(`/docker/projects/${projectId}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusBadge.className = 'docker-status-badge docker-status-running';
                    statusBadge.innerHTML = '<i class="fas fa-circle"></i> Running';
                    
                    // Replace placeholder with live iframe
                    const previewWrapper = document.getElementById(`dockerPreviewFrame${projectId}`)?.parentElement || 
                                        document.querySelector(`#dockerAccordion${projectId} .docker-preview-placeholder`).parentElement;
                    previewWrapper.innerHTML = `
                        <iframe id="dockerPreviewFrame${projectId}" 
                                src="http://localhost:${data.app_port || 8080}" 
                                class="docker-preview-frame" frameborder="0">
                        </iframe>
                    `;
                    
                    // Update URL display
                    document.getElementById(`dockerPreviewUrl${projectId}`).textContent = `http://localhost:${data.app_port || 8080}`;
                } else {
                    alert('Start failed: ' + (data.message || 'Unknown error'));
                    statusBadge.innerHTML = '<i class="fas fa-circle"></i> Stopped';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start project');
            });
        }


        function dockerStopProject(projectId) {
            // AJAX call to stop project
            alert('Stopping project ' + projectId);
        }

        function dockerRestartProject(projectId) {
            // AJAX call to restart project
            alert('Restarting project ' + projectId);
        }

        function dockerViewLogs(projectId) {
            // Open logs modal or page
            alert('Viewing logs for project ' + projectId);
        }

        function dockerDownloadFiles(projectId) {
            // Download project files
            window.location.href = '/docker/projects/' + projectId + '/download';
        }

        function dockerDeleteProject(projectId) {
            if (confirm('Are you sure you want to delete this project?')) {
                // AJAX call to delete project
                alert('Deleting project ' + projectId);
            }
        }
    </script>

</x-app-layout>
