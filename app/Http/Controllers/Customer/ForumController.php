<?php

namespace App\Http\Controllers\Customer;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('categoria');

        $topics = ForumTopic::query()
            ->visible()
            ->with(['author', 'category'])
            ->withCount(['replies' => fn (Builder $query) => $query->visible()])
            ->when($category, fn (Builder $query) => $query->whereRelation('category', 'slug', $category))
            ->latest()
            ->get();

        return view('customer.forum.index', [
            'topics' => $topics,
            'categories' => ForumCategory::query()->orderBy('name')->get(),
            'category' => $category,
        ]);
    }

    public function create(): View
    {
        return view('customer.forum.create', [
            'categories' => ForumCategory::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:8', 'max:120'],
            'forum_category_id' => ['required', Rule::exists('forum_categories', 'id')],
            'body' => ['required', 'string', 'min:20', 'max:3000'],
        ], [
            'title.min' => 'Escribí un título de al menos 8 caracteres, así se entiende de qué va.',
            'forum_category_id.required' => 'Elegí una categoría.',
            'body.min' => 'Contá un poco más: al menos 20 caracteres.',
        ]);

        $topic = $request->user()->forumTopics()->create($data);

        return redirect()->route('forum.show', $topic)->with('status', 'Publicaste el tema.');
    }

    public function show(ForumTopic $topic): View
    {
        abort_if($topic->removed_at, 404);

        return view('customer.forum.show', [
            'topic' => $topic->load(['author', 'category']),
            'replies' => $topic->replies()->visible()->with('author')->oldest()->get(),
            'reasons' => ForumReport::REASONS,
        ]);
    }

    public function reply(Request $request, ForumTopic $topic): RedirectResponse
    {
        abort_if($topic->removed_at, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'body.required' => 'Escribí tu respuesta antes de publicarla.',
        ]);

        $reply = $topic->replies()->create([...$data, 'user_id' => $request->user()->id]);

        return back()->withFragment("respuesta-{$reply->id}")->with('status', 'Publicaste tu respuesta.');
    }

    public function report(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['topic', 'reply'])],
            'id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(ForumReport::REASONS))],
            'details' => ['nullable', 'string', 'max:255'],
        ], [
            'reason.required' => 'Elegí un motivo para la denuncia.',
        ]);

        $reportable = $data['type'] === 'topic'
            ? ForumTopic::query()->visible()->findOrFail($data['id'])
            : ForumReply::query()->visible()->findOrFail($data['id']);

        $reportable->reports()->create([
            'user_id' => $request->user()->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
        ]);

        User::query()->where('role', UserRole::Admin)->get()->each->notify(new ActivityNotification(
            title: 'Hay una denuncia nueva en el foro',
            body: ForumReport::REASONS[$data['reason']],
            url: route('admin.reports.index', absolute: false),
            icon: 'flag',
        ));

        return back()->with('status', 'Recibimos tu denuncia. La va a revisar el equipo de moderación.');
    }
}
