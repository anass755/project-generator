<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    /**
     * LLM driver: 'ollama' | 'openai' | 'openrouter'
     */
    private string $driver;
    private string $model;

public function __construct()
{
    $this->driver = env('AI_DRIVER', 'ollama');
    $this->model  = env('AI_MODEL', 'llama3.2');
}

    // ─────────────────────────────────────────────
    //  MAIN ENTRY
    // ─────────────────────────────────────────────

    public function ask(Request $request)
    {
        try {
            $request->validate(['question' => 'required|string|max:1000']);

            $userMessage = trim($request->input('question'));

            // Load conversation history from session (array of {role, content})
            $history = session('chat_history', []);

            // Detect intent to decide what context to inject
            $intent  = $this->detectIntent(strtolower($userMessage));
            $context = $this->buildContext($intent);

            // Build the full messages array for the LLM
            $messages = $this->buildMessages($history, $context, $userMessage);

            // Call the LLM
            $aiReply = $this->callLLM($messages);

            // Persist the new exchange into session history (keep last 10 pairs = 20 items)
            $history[] = ['role' => 'user',      'content' => $userMessage];
            $history[] = ['role' => 'assistant', 'content' => $aiReply];
            $history   = array_slice($history, -20);
            session(['chat_history' => $history]);

            return response()->json([
                'success'     => true,
                'answer'      => $aiReply,
                'suggestions' => $this->buildSuggestions($intent, $aiReply),
            ]);

        } catch (\Exception $e) {
            Log::error('AiChat error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'answer'  => '❌ Something went wrong. Please try again.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    //  INTENT DETECTION  (lightweight, no hardcoded replies)
    // ─────────────────────────────────────────────

    private function detectIntent(string $q): string
    {
        $dbKeywords = [
            'how many','count','total','project','docker project',
            'active','running','failed','completed','latest project',
            'show project','list project','created today','this week',
            'this month','find project','search project','status',
        ];
        foreach ($dbKeywords as $kw) {
            if (str_contains($q, $kw)) return 'db';
        }

        $webKeywords = [
            'what is','how to','explain','define','search','look up',
            'tell me about','latest news','best practice','tutorial',
            'difference between','vs','compare','why','when did',
        ];
        foreach ($webKeywords as $kw) {
            if (str_contains($q, $kw)) return 'web';
        }

        return 'general'; // casual or ambiguous — LLM handles it natively
    }

    // ─────────────────────────────────────────────
    //  CONTEXT BUILDER  — inject DB stats or web snippets
    // ─────────────────────────────────────────────

    private function buildContext(string $intent): string
    {
        if ($intent === 'db') {
            return $this->buildDbContext();
        }

        if ($intent === 'web') {
            return $this->buildWebContext();
        }

        return ''; // no extra context needed for casual chat
    }

    private function buildDbContext(): string
    {
        $total     = DB::table('docker_projects')->count();
        $active    = DB::table('docker_projects')->where('status', 'active')->count();
        $failed    = DB::table('docker_projects')->where('status', 'failed')->count();
        $completed = DB::table('docker_projects')->where('status', 'completed')->count();
        $today     = DB::table('docker_projects')->whereDate('created_at', today())->count();
        $thisWeek  = DB::table('docker_projects')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = DB::table('docker_projects')->whereMonth('created_at', now()->month)->count();

        $latest = DB::table('docker_projects')->latest('created_at')->first();
        $latestStr = $latest
            ? "Name: {$latest->project_name}, Status: {$latest->status}, Created: " . date('M d Y H:i', strtotime($latest->created_at))
            : 'None';

        $recentList = DB::table('docker_projects')->latest()->take(5)->get()
            ->map(fn($p) => "- {$p->project_name} [{$p->status}]")->implode("\n");

        return <<<CONTEXT
        [LIVE DATABASE STATS — Docker Projects]
        Total projects   : {$total}
        Active           : {$active}
        Failed           : {$failed}
        Completed        : {$completed}
        Created today    : {$today}
        Created this week: {$thisWeek}
        Created this month: {$thisMonth}
        Latest project   : {$latestStr}

        Recent 5 projects:
        {$recentList}
        CONTEXT;
    }

    private function buildWebContext(): string
    {
        // Only runs if SERPER_API_KEY is set
        $apiKey = env('SERPER_API_KEY');
        if (!$apiKey) return '';

        // We'll inject the question from the caller in buildMessages instead
        // Here we return a marker so the LLM knows web search is available
        return '[WEB_SEARCH_ENABLED]';
    }

    // ─────────────────────────────────────────────
    //  MESSAGE BUILDER — system prompt + history + user turn
    // ─────────────────────────────────────────────

    private function buildMessages(array $history, string $context, string $userMessage): array
    {
        $systemPrompt = $this->buildSystemPrompt($context, $userMessage);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Append conversation history (already formatted as role/content pairs)
        foreach ($history as $turn) {
            $messages[] = $turn;
        }

        // Latest user turn
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function buildSystemPrompt(string $context, string $userMessage): string
    {
        $base = <<<SYSTEM
        You are an intelligent AI assistant embedded in a Docker Project Generator dashboard.
        You are helpful, conversational, and precise. You respond naturally — like a knowledgeable colleague, not a search engine.

        Guidelines:
        - For casual greetings or chitchat, reply warmly and naturally. You may use emojis sparingly.
        - For Docker project questions, use the live stats provided below to give exact, accurate answers.
        - For technical questions (how-to, concepts, commands), give clear, actionable answers from your training knowledge.
        - If web search results are provided, use them to give up-to-date answers and cite the source.
        - Never make up database numbers. Only state stats that are explicitly provided in the context below.
        - Keep answers concise but complete. Use bullet points when listing multiple items.
        - You remember the ongoing conversation — reference prior messages when relevant.
        SYSTEM;

        if (!empty($context) && $context !== '[WEB_SEARCH_ENABLED]') {
            $base .= "\n\n" . $context;
        }

        // If web search is enabled, fetch and inject results now
        if ($context === '[WEB_SEARCH_ENABLED]') {
            $webResults = $this->fetchWebResults($userMessage);
            if ($webResults) {
                $base .= "\n\n[WEB SEARCH RESULTS]\n" . $webResults;
                $base .= "\n\nUse the above web results to answer the user's question accurately. Mention the source URL briefly.";
            }
        }

        return $base;
    }

    // ─────────────────────────────────────────────
    //  WEB SEARCH (Serper)
    // ─────────────────────────────────────────────

    private function fetchWebResults(string $question): string
    {
        $apiKey = env('SERPER_API_KEY');
        if (!$apiKey) return '';

        try {
            $response = Http::timeout(8)->withHeaders([
                'X-API-KEY'    => $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://google.serper.dev/search', [
                'q'   => $question,
                'gl'  => 'in',
                'hl'  => 'en',
                'num' => 4,
            ]);

            if (!$response->successful()) return '';

            $data    = $response->json();
            $results = '';

            if (!empty($data['answerBox']['answer'])) {
                $results .= "Quick Answer: " . $data['answerBox']['answer'] . "\n";
            } elseif (!empty($data['answerBox']['snippet'])) {
                $results .= "Quick Answer: " . $data['answerBox']['snippet'] . "\n";
            }

            if (!empty($data['organic'])) {
                foreach (array_slice($data['organic'], 0, 3) as $item) {
                    $results .= "\n[{$item['title']}]\n";
                    if (!empty($item['snippet'])) $results .= $item['snippet'] . "\n";
                    $results .= "Source: {$item['link']}\n";
                }
            }

            return $results ?: '';

        } catch (\Exception $e) {
            Log::warning('Web search failed: ' . $e->getMessage());
            return '';
        }
    }

    // ─────────────────────────────────────────────
    //  LLM DISPATCHER
    // ─────────────────────────────────────────────

    private function callLLM(array $messages): string
    {
        return match ($this->driver) {
            'openai'     => $this->callOpenAI($messages),
            'openrouter' => $this->callOpenRouter($messages),
            default      => $this->callOllama($messages),
        };
    }

    /**
     * Ollama (local) — free, runs on your machine
     * Recommended models: llama3.2, mistral, gemma2, qwen2.5
     */
    private function callOllama(array $messages): string
    {
        $response = Http::timeout(60)->post(env('OLLAMA_HOST', 'http://localhost:11434') . '/api/chat', [
            'model'    => $this->model,
            'messages' => $messages,
            'stream'   => false,
            'options'  => [
                'temperature' => 0.7,
                'num_ctx'     => 4096,
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Ollama request failed: ' . $response->status());
        }

        return $response->json('message.content')
            ?? throw new \RuntimeException('Ollama returned empty response.');
    }

    /**
     * OpenAI (paid, most capable)
     */
    private function callOpenAI(array $messages): string
    {
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model'       => $this->model ?: 'gpt-4o-mini',
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 800,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI request failed: ' . $response->status());
        }

        return $response->json('choices.0.message.content')
            ?? throw new \RuntimeException('OpenAI returned empty response.');
    }

    /**
     * OpenRouter — access GPT-4, Claude, Mistral, Gemma with one key
     * Free tier available: https://openrouter.ai
     */
    private function callOpenRouter(array $messages): string
    {
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => env('APP_URL', 'http://localhost'),
            'X-Title'       => 'Docker Project Generator AI',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'       => $this->model ?: 'google/gemma-3-27b-it:free',
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 800,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenRouter request failed: ' . $response->status());
        }

        return $response->json('choices.0.message.content')
            ?? throw new \RuntimeException('OpenRouter returned empty response.');
    }

    // ─────────────────────────────────────────────
    //  SMART SUGGESTIONS  (still rule-based, fast)
    // ─────────────────────────────────────────────

    private function buildSuggestions(string $intent, string $aiReply): array
    {
        return match ($intent) {
            'db'  => [
                'Show me the last 5 projects',
                'How many projects failed this month?',
                'What is the most recent active project?',
            ],
            'web' => [
                'Can you give me a step-by-step example?',
                'What are the best practices for this?',
                'Show me a code example',
            ],
            default => [
                'How many Docker projects are running?',
                'Show me the latest project',
                'What can you help me with?',
            ],
        };
    }

    // ─────────────────────────────────────────────
    //  UTILITY: clear conversation history
    // ─────────────────────────────────────────────

    public function clearHistory()
    {
        session()->forget('chat_history');
        return response()->json(['success' => true, 'message' => 'Conversation cleared.']);
    }
}
