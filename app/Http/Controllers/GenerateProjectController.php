<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DockerGeneratorService;
use App\Models\DockerProject;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GenerateProjectController extends Controller
{
    protected $dockerGenerator;

    public function __construct(DockerGeneratorService $dockerGenerator)
    {
        $this->dockerGenerator = $dockerGenerator;
    }
    public function index()
    {
        $projects = DockerProject::orderBy('created_at', 'desc')->get();
        return view('docker.index', compact('projects'));
    }
    public function create()
    {
        return view('docker.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
                'project_name' => 'required|alpha_dash|max:50|unique:docker_projects,project_name',
                'domain_name' => 'required|string|max:100',
                'php_version' => 'required|in:8.2,8.3,8.4',
                'mysql_version' => 'required|in:5.7,8.0,8.2',
                'node_version' => 'nullable|in:18,20',
                'app_port' => 'required|numeric|min:1024|max:65535|different:phpmyadmin_port',
                'phpmyadmin_port' => 'required|numeric|min:1024|max:65535',
                'db_username' => 'required|string|max:50',
                'db_password' => 'required|string|min:6',
                'db_root_password' => 'required|string|min:6|different:db_password',
            ], [
                'project_name.unique' => 'A project with this name already exists.',
                'app_port.different' => 'Application port must be different from PHPMyAdmin port.',
                'db_root_password.different' => 'Root password must be different from database password.',
        ]);
        $validated['include_redis'] = $request->boolean('include_redis');
        $validated['include_mailhog'] = $request->boolean('include_mailhog');
        $validated['include_node'] = $request->boolean('include_node');
        $validated['status'] = 1;

        try {
            
                $zipPath = $this->dockerGenerator->generate($validated);
                $project = DockerProject::create($validated);

            return response()->download($zipPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()
                ->route('docker.create')
                ->withErrors(['error' => 'Failed to generate project: ' . $e->getMessage()])
                ->withInput();
        }
    }

    

}
