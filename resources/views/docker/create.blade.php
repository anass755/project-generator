<x-app-layout>
    <x-slot:title>Create Docker Project</x-slot:title>
    <x-slot:pageTitle>Create New Docker Project</x-slot:pageTitle>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                {{-- Summary Card --}}
                <div class="docker-summary-card" id="dockerSummaryCard" style="display: none;">
                    <div class="docker-summary-header">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span>Your Configuration</span>
                        <button type="button" class="docker-btn-close-summary" onclick="dockerToggleSummary()">
                            <i class="fas fa-chevron-up" id="dockerSummaryIcon"></i>
                        </button>
                    </div>
                    <div class="docker-summary-body" id="dockerSummaryBody">
                        <div class="docker-summary-grid">
                            <div class="docker-summary-item" id="dockerSummaryProject" style="display: none;">
                                <i class="fas fa-tag text-primary"></i>
                                <div>
                                    <small>Project Name</small>
                                    <strong id="dockerDisplayProjectName">-</strong>
                                </div>
                            </div>
                            <div class="docker-summary-item" id="dockerSummaryDomain" style="display: none;">
                                <i class="fas fa-globe text-primary"></i>
                                <div>
                                    <small>Domain</small>
                                    <strong id="dockerDisplayDomain">-</strong>
                                </div>
                            </div>
                            <div class="docker-summary-item" id="dockerSummaryPHP" style="display: none;">
                                <i class="fab fa-php text-info"></i>
                                <div>
                                    <small>PHP</small>
                                    <strong id="dockerDisplayPHP">-</strong>
                                </div>
                            </div>
                            <div class="docker-summary-item" id="dockerSummaryMySQL" style="display: none;">
                                <i class="fas fa-database text-warning"></i>
                                <div>
                                    <small>MySQL</small>
                                    <strong id="dockerDisplayMySQL">-</strong>
                                </div>
                            </div>
                            <div class="docker-summary-item" id="dockerSummaryPorts" style="display: none;">
                                <i class="fas fa-network-wired text-danger"></i>
                                <div>
                                    <small>Ports</small>
                                    <strong id="dockerDisplayPorts">-</strong>
                                </div>
                            </div>
                            <div class="docker-summary-item" id="dockerSummaryDB" style="display: none;">
                                <i class="fas fa-key text-success"></i>
                                <div>
                                    <small>DB User</small>
                                    <strong id="dockerDisplayDBUser">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('docker.store') }}" method="POST" id="dockerForm">
                    @csrf
                    {{-- Hidden fields for default services --}}
                    <input type="hidden" name="include_redis" value="1">
                    <input type="hidden" name="include_mailhog" value="1">

                    {{-- Step 1: Project Name --}}
                    <div class="docker-step-section active" id="dockerStep1">
                        <div class="docker-step-number">Step 1 of 4</div>
                        <h3 class="docker-step-title">
                            <i class="fas fa-tag text-primary me-2"></i>
                            What's your project name?
                        </h3>
                        <input 
                            type="text" 
                            name="project_name" 
                            id="docker_project_name"
                            class="form-control form-control-lg @error('project_name') is-invalid @enderror" 
                            placeholder="e.g., myproject" 
                            value="{{ old('project_name') }}"
                            pattern="[a-z0-9-]+"
                            required
                        >
                        @error('project_name')
                            <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                        @else
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>Only lowercase letters, numbers, and dashes
                            </small>
                        @enderror
                        <button type="button" class="btn btn-primary mt-3" onclick="dockerNextStep(1)">
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>

                    {{-- Step 2: Domain & Tech Stack --}}
                    <div class="docker-step-section" id="dockerStep2">
                        <div class="docker-step-number">Step 2 of 4</div>
                        <h3 class="docker-step-title">
                            <i class="fas fa-layer-group text-primary me-2"></i>
                            Configure your environment
                        </h3>
                        
                        <div class="mb-3">
                            <label class="form-label">Domain Name</label>
                            <input 
                                type="text" 
                                name="domain_name" 
                                id="docker_domain_name"
                                class="form-control @error('domain_name') is-invalid @enderror" 
                                placeholder="localhost" 
                                value="{{ old('domain_name', 'localhost') }}"
                                required
                            >
                            @error('domain_name')
                                <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fab fa-php me-1"></i>PHP Version
                                </label>
                                <select name="php_version" id="docker_php_version" class="form-select @error('php_version') is-invalid @enderror" required>
                                    <option value="8.4" {{ old('php_version', '8.4') == '8.4' ? 'selected' : '' }}>8.4</option>
                                    <option value="8.3" {{ old('php_version') == '8.3' ? 'selected' : '' }}>8.3</option>
                                    <option value="8.2" {{ old('php_version') == '8.2' ? 'selected' : '' }}>8.2</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-database me-1"></i>MySQL Version
                                </label>
                                <select name="mysql_version" id="docker_mysql_version" class="form-select @error('mysql_version') is-invalid @enderror" required>
                                    <option value="8.2" {{ old('mysql_version', '8.2') == '8.2' ? 'selected' : '' }}>8.2</option>
                                    <option value="8.0" {{ old('mysql_version') == '8.0' ? 'selected' : '' }}>8.0</option>
                                    <option value="5.7" {{ old('mysql_version') == '5.7' ? 'selected' : '' }}>5.7</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" onclick="dockerPrevStep(2)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn btn-primary" onclick="dockerNextStep(2)">
                                Continue <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Step 3: Ports --}}
                    <div class="docker-step-section" id="dockerStep3">
                        <div class="docker-step-number">Step 3 of 4</div>
                        <h3 class="docker-step-title">
                            <i class="fas fa-network-wired text-primary me-2"></i>
                            Configure ports
                        </h3>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Application Port</label>
                                <input 
                                    type="number" 
                                    name="app_port" 
                                    id="docker_app_port"
                                    class="form-control text-center @error('app_port') is-invalid @enderror" 
                                    value="{{ old('app_port', 8080) }}"
                                    min="1024"
                                    max="65535"
                                    required
                                >
                                @error('app_port')
                                    <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @else
                                    <small class="text-primary d-block mt-1">http://localhost:8080</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PHPMyAdmin Port</label>
                                <input 
                                    type="number" 
                                    name="phpmyadmin_port" 
                                    id="docker_phpmyadmin_port"
                                    class="form-control text-center @error('phpmyadmin_port') is-invalid @enderror" 
                                    value="{{ old('phpmyadmin_port', 8081) }}"
                                    min="1024"
                                    max="65535"
                                    required
                                >
                                @error('phpmyadmin_port')
                                    <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @else
                                    <small class="text-primary d-block mt-1">http://localhost:8081</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="dockerPrevStep(3)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="button" class="btn btn-primary" onclick="dockerNextStep(3)">
                                Continue <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Step 4: Database Credentials --}}
                    <div class="docker-step-section" id="dockerStep4">
                        <div class="docker-step-number">Step 4 of 4</div>
                        <h3 class="docker-step-title">
                            <i class="fas fa-key text-primary me-2"></i>
                            Database credentials
                        </h3>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">DB Username</label>
                                <input 
                                    type="text" 
                                    name="db_username" 
                                    id="docker_db_username"
                                    class="form-control @error('db_username') is-invalid @enderror" 
                                    placeholder="projectuser"
                                    value="{{ old('db_username') }}"
                                    required
                                >
                                @error('db_username')
                                    <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DB Password</label>
                                <input 
                                    type="text" 
                                    name="db_password" 
                                    class="form-control @error('db_password') is-invalid @enderror" 
                                    placeholder="Password123"
                                    value="{{ old('db_password') }}"
                                    minlength="6"
                                    required
                                >
                                @error('db_password')
                                    <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Root Password</label>
                                <input 
                                    type="text" 
                                    name="db_root_password" 
                                    class="form-control @error('db_root_password') is-invalid @enderror" 
                                    placeholder="RootPass123"
                                    value="{{ old('db_root_password') }}"
                                    minlength="6"
                                    required
                                >
                                @error('db_root_password')
                                    <div class="docker-error-msg"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="dockerPrevStep(4)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i>Generate Project
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <style>
        .docker-summary-card{background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%);border:2px solid #3b82f6;border-radius:16px;margin-bottom:24px;overflow:hidden;animation:dockerSlideDown .4s ease}
        @keyframes dockerSlideDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
        .docker-summary-header{display:flex;align-items:center;padding:16px 20px;background:#fff;border-bottom:2px solid #dbeafe;font-weight:700;color:#1e40af}
        .docker-summary-header span{flex:1}
        .docker-btn-close-summary{background:0 0;border:none;color:#6b7280;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .3s ease}
        .docker-btn-close-summary:hover{background:#f3f4f6;color:#1f2937}
        .docker-summary-body{padding:20px;transition:all .3s ease}
        .docker-summary-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
        .docker-summary-item{display:flex;align-items:center;gap:12px;background:#fff;padding:14px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .docker-summary-item i{font-size:24px}
        .docker-summary-item small{display:block;font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;letter-spacing:.5px}
        .docker-summary-item strong{display:block;font-size:15px;color:#1a1a2e;font-weight:700}
        .docker-step-section{display:none;background:#fff;padding:40px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.08);animation:dockerFadeIn .4s ease}
        .docker-step-section.active{display:block}
        @keyframes dockerFadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .docker-step-number{font-size:13px;font-weight:600;color:#667eea;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
        .docker-step-title{font-size:28px;font-weight:700;color:#1a1a2e;margin-bottom:24px}
        .docker-error-msg{color:#dc2626;font-size:13px;font-weight:600;margin-top:8px;padding:8px 12px;background:#fef2f2;border-left:3px solid #ef4444;border-radius:6px}
    </style>

    <script>
        function dockerUpdateSummary(){const a=document.getElementById("docker_project_name").value;a&&(document.getElementById("dockerDisplayProjectName").textContent=a,document.getElementById("dockerSummaryProject").style.display="flex");const b=document.getElementById("docker_domain_name")?.value;b&&(document.getElementById("dockerDisplayDomain").textContent=b,document.getElementById("dockerSummaryDomain").style.display="flex");const c=document.getElementById("docker_php_version")?.value;c&&(document.getElementById("dockerDisplayPHP").textContent="PHP "+c,document.getElementById("dockerSummaryPHP").style.display="flex");const d=document.getElementById("docker_mysql_version")?.value;d&&(document.getElementById("dockerDisplayMySQL").textContent="MySQL "+d,document.getElementById("dockerSummaryMySQL").style.display="flex");const e=document.getElementById("docker_app_port")?.value,f=document.getElementById("docker_phpmyadmin_port")?.value;e&&f&&(document.getElementById("dockerDisplayPorts").textContent=e+" / "+f,document.getElementById("dockerSummaryPorts").style.display="flex");const g=document.getElementById("docker_db_username")?.value;g&&(document.getElementById("dockerDisplayDBUser").textContent=g,document.getElementById("dockerSummaryDB").style.display="flex"),document.getElementById("dockerSummaryCard").style.display="block",setTimeout(function(){const a=document.getElementById("dockerSummaryBody"),b=document.getElementById("dockerSummaryIcon");a.style.display="none",b.classList.remove("fa-chevron-up"),b.classList.add("fa-chevron-down")},2e3)}function dockerNextStep(a){const b=document.getElementById("dockerStep"+a),c=b.querySelectorAll("input[required], select[required]");let d=!0;c.forEach(a=>{a.value?a.classList.remove("is-invalid"):(d=!1,a.classList.add("is-invalid"))}),d&&(dockerUpdateSummary(),b.classList.remove("active"),document.getElementById("dockerStep"+(a+1)).classList.add("active"),window.scrollTo({top:0,behavior:"smooth"}),setTimeout(function(){const b=document.getElementById("dockerStep"+(a+1)).querySelector("input:not([type=\"checkbox\"]), select");b&&b.focus()},500))}function dockerPrevStep(a){document.getElementById("dockerStep"+a).classList.remove("active"),document.getElementById("dockerStep"+(a-1)).classList.add("active"),window.scrollTo({top:0,behavior:"smooth"})}function dockerToggleSummary(){const a=document.getElementById("dockerSummaryBody"),b=document.getElementById("dockerSummaryIcon");"none"===a.style.display?(a.style.display="block",b.classList.remove("fa-chevron-down"),b.classList.add("fa-chevron-up")):(a.style.display="none",b.classList.remove("fa-chevron-up"),b.classList.add("fa-chevron-down"))}@if($errors->any())document.addEventListener("DOMContentLoaded",function(){@if($errors->has("project_name"))document.getElementById("dockerStep1").classList.add("active")@elseif($errors->has(["domain_name","php_version","mysql_version"]))document.getElementById("dockerStep2").classList.add("active")@elseif($errors->has(["app_port","phpmyadmin_port"]))document.getElementById("dockerStep3").classList.add("active")@else document.getElementById("dockerStep4").classList.add("active")@endif});@endif
    </script>

</x-app-layout>
