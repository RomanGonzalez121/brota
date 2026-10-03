<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumReply;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * M10: moderation of the recycling forum. Reports are read by content, not
 * one by one: three people reporting the same topic are one decision.
 */
class ReportController extends Controller
{
    public const PENDING = 'pending';

    public const REMOVED = 'removed';

    public const DISMISSED = 'dismissed';

    public function index(): View
    {
        $withContent = fn (MorphTo $content) => $content->morphWith([
            ForumTopic::class => ['author', 'category'],
            ForumReply::class => ['author', 'topic'],
        ]);

        $pending = ForumReport::query()
            ->where('status', self::PENDING)
            ->with(['reporter', 'reportable' => $withContent])
            ->oldest()
            ->get()
            ->filter(fn (ForumReport $report) => $report->reportable !== null)
            ->groupBy(fn (ForumReport $report) => $report->reportable_type.':'.$report->reportable_id)
            ->values();

        $resolved = ForumReport::query()
            ->where('status', '!=', self::PENDING)
            ->with(['reviewer', 'reportable' => $withContent])
            ->latest('reviewed_at')
            ->get()
            ->filter(fn (ForumReport $report) => $report->reportable !== null)
            ->unique(fn (ForumReport $report) => $report->reportable_type.':'.$report->reportable_id.':'.$report->reviewed_at)
            ->take(8)
            ->values();

        // Decisions of the month, counted by content like everything else here.
        $decided = ForumReport::query()
            ->where('reviewed_at', '>=', now()->startOfMonth())
            ->get(['reportable_type', 'reportable_id', 'status'])
            ->unique(fn (ForumReport $report) => $report->reportable_type.':'.$report->reportable_id.':'.$report->status)
            ->countBy('status');

        return view('admin.reports', [
            'pending' => $pending,
            'resolved' => $resolved,
            'removedThisMonth' => $decided->get(self::REMOVED, 0),
            'dismissedThisMonth' => $decided->get(self::DISMISSED, 0),
        ]);
    }

    /**
     * Take the reported content down. Its author and whoever reported it are told.
     */
    public function remove(Request $request, ForumReport $report): RedirectResponse
    {
        $content = $report->reportable;
        abort_if($content === null, 404);

        $reports = $this->close($request, $report, self::REMOVED);
        $content->update(['removed_at' => now()]);

        $content->author->notify(new ActivityNotification(
            title: $content instanceof ForumTopic ? 'Dimos de baja tu tema del foro' : 'Dimos de baja tu respuesta en el foro',
            body: 'Motivo: '.mb_strtolower($report->reasonLabel()).'.',
            url: route('forum.index', absolute: false),
            icon: 'flag',
        ));

        $this->tellReporters($reports, 'Dimos de baja el contenido que denunciaste. Gracias por avisar.');

        return back()->with('status', 'Diste de baja el contenido. Ya no se ve en el foro.');
    }

    /**
     * The content stays: the reports did not hold.
     */
    public function dismiss(Request $request, ForumReport $report): RedirectResponse
    {
        abort_if($report->reportable === null, 404);

        $reports = $this->close($request, $report, self::DISMISSED);
        $this->tellReporters($reports, 'Revisamos el contenido que denunciaste y lo dejamos publicado.');

        return back()->with('status', 'Descartaste la denuncia. El contenido sigue publicado.');
    }

    /**
     * Undo a removal: the content goes back to the forum.
     */
    public function restore(Request $request, ForumReport $report): RedirectResponse
    {
        $content = $report->reportable;
        abort_if($content === null || $report->status !== self::REMOVED, 404);

        $content->update(['removed_at' => null]);

        $content->reports()->where('status', self::REMOVED)->update([
            'status' => self::DISMISSED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $content->author->notify(new ActivityNotification(
            title: $content instanceof ForumTopic ? 'Tu tema volvió al foro' : 'Tu respuesta volvió al foro',
            body: 'Lo revisamos de nuevo y lo restauramos.',
            url: route('forum.index', absolute: false),
            icon: 'check',
        ));

        return back()->with('status', 'Restauraste el contenido. Vuelve a verse en el foro.');
    }

    /**
     * Settles every pending report on the same content with one decision.
     *
     * @return Collection<int, ForumReport>
     */
    private function close(Request $request, ForumReport $report, string $status): Collection
    {
        abort_unless($report->status === self::PENDING, 409);

        $reports = $report->reportable->reports()->where('status', self::PENDING)->with('reporter')->get();

        ForumReport::query()->whereKey($reports->modelKeys())->update([
            'status' => $status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return $reports;
    }

    /**
     * @param  Collection<int, ForumReport>  $reports
     */
    private function tellReporters(Collection $reports, string $body): void
    {
        $reports->pluck('reporter')->filter()->unique('id')->each->notify(new ActivityNotification(
            title: 'Revisamos tu denuncia',
            body: $body,
            url: route('forum.index', absolute: false),
            icon: 'flag',
        ));
    }
}
