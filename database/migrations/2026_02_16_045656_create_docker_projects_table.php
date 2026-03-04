<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiChatController extends Controller
{
    public function ask(Request $request)
    {
        try {
            $request->validate(['question' => 'required|string|max:500']);

            $question = strtolower(trim($request->question));
            $question = preg_replace('/[^a-z0-9\s]/', '', $question);

            \Log::info('Chat Question: ' . $question);

            $answer = $this->processQuestion($question);

            return response()->json([
                'success' => true,
                'answer'  => $answer,
            ]);

        } catch (\Exception $e) {
            \Log::error('Chat Error: ' . $e->getMessage() . ' Line:' . $e->getLine());

            return response()->json([
                'success' => true,
                'answer'  => '❌ Error: ' . $e->getMessage(),
            ]);
        }
    }

    private function processQuestion(string $q): string
    {
        // ── HOW MANY / COUNT PROJECTS ──
        if ($this->any($q, ['how many', 'count', 'total', 'number of']) &&
            $this->any($q, ['project', 'docker', 'app'])) {
            $count = DB::table('docker_projects')->count();
            return "🐳 There are **{$count} Docker projects** created in total.";
        }

        // ── JUST TOTAL (general) ──
        if ($this->any($q, ['how many', 'total', 'count'])) {
            $count = DB::table('docker_projects')->count();
            return "🐳 Total Docker projects: **{$count}**.";
        }

        // ── LIST / SHOW ALL ──
        if ($this->any($q, ['show all', 'list all', 'all project', 'show project', 'list project', 'show me'])) {
            $projects = DB::table('docker_projects')->latest()->take(5)->get();
            if ($projects->isEmpty()) return "📭 No projects found.";
            $list = $projects->map(fn($p) => "• {$p->name}")->join("\n");
            return "📋 Last 5 projects:\n{$list}";
        }

        // ── ACTIVE / RUNNING ──
        if ($this->any($q, ['active', 'running', 'live', 'online', 'up'])) {
            $count = DB::table('docker_projects')->where('status', 'active')->count();
            $names = DB::table('docker_projects')->where('status', 'active')->pluck('name')->take(3);
            $list  = $names->isNotEmpty() ? "\n" . $names->map(fn($n) => "• {$n}")->join("\n") : '';
            return "🟢 **{$count} active projects**.{$list}";
        }

        // ── FAILED ──
        if ($this->any($q, ['fail', 'failed', 'error', 'broken', 'down'])) {
            $count = DB::table('docker_projects')->where('status', 'failed')->count();
            return "❌ **{$count} projects** have failed status.";
        }

        // ── LATEST / NEWEST ──
        if ($this->any($q, ['latest', 'newest', 'recent', 'last', 'new'])) {
            $project = DB::table('docker_projects')->latest('created_at')->first();
            if (!$project) return "⚠️ No projects found.";
            return "🆕 Latest: **{$project->name}**\n📅 Created: " . date('M d, Y h:i A', strtotime($project->created_at));
        }

        // ── COMPLETED ──
        if ($this->any($q, ['complete', 'completed', 'done', 'finished', 'success'])) {
            $count = DB::table('docker_projects')->where('status', 'completed')->count();
            return "✅ **{$count} projects** completed successfully.";
        }

        // ── TODAY ──
        if ($this->any($q, ['today', 'this day'])) {
            $count = DB::table('docker_projects')->whereDate('created_at', today())->count();
            return "📅 **{$count} projects** created today.";
        }

        // ── THIS WEEK ──
        if ($this->any($q, ['this week', 'week', '7 days'])) {
            $count = DB::table('docker_projects')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
            return "📅 **{$count} projects** created this week.";
        }

        // ── THIS MONTH ──
        if ($this->any($q, ['this month', 'month', '30 days'])) {
            $count = DB::table('docker_projects')
                ->whereMonth('created_at', now()->month)
                ->count();
            return "📅 **{$count} projects** created this month.";
        }

        // ── FIND BY NAME ──
        if ($this->any($q, ['find', 'search', 'where is', 'locate'])) {
            preg_match('/(?:find|search|locate|where is)\s+(.+)/', $q, $matches);
            if (!empty($matches[1])) {
                $name    = trim($matches[1]);
                $project = DB::table('docker_projects')
                    ->where('name', 'like', "%{$name}%")
                    ->first();
                if ($project) {
                    return "🔍 Found: **{$project->name}**\n📌 Status: {$project->status}\n📅 Created: " . date('M d, Y', strtotime($project->created_at));
                }
                return "🔍 No project found matching **{$name}**.";
            }
        }

        // ── FALLBACK ──
        return "🤔 Try asking:\n" .
               "• \"How many projects created?\"\n" .
               "• \"Show active projects\"\n" .
               "• \"Latest project\"\n" .
               "• \"How many failed?\"\n" .
               "• \"Projects today\"\n" .
               "• \"Find [project name]\"";
    }

    private function any(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) return true;
        }
        return false;
    }
}
